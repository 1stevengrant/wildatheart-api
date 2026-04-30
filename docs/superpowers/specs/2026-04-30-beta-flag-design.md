# Beta Flag Design

**Date:** 2026-04-30
**Status:** Approved (pending spec review)
**Owner:** Steven Grant

## Summary

Introduce a single rolling `BetaFeature` Pennant flag that determines whether an authenticated user receives "beta" or "standard" variants of API response payloads. Membership has two additive sources: the `beta-tester` Spatie role (bulk) and a per-user `beta_enabled` boolean column on `users` (individual override). Membership is toggled via the existing Statamic CP through the Runway User resource. The flag does not gate route access. Branching lives inside Data Transfer Objects and Builders.

## Goals

- Provide a single, reusable feature flag for the rolling Wild at Heart beta program.
- Allow administrators to manage beta cohorts in the Statamic CP without code deploys.
- Support both bulk (role-based) and individual (per-user override) membership.
- Vary API JSON response shape based on beta membership at the DTO / Builder layer.
- Keep cached Pennant values consistent when membership changes via observers.

## Non-Goals

- Multiple discrete feature flags. The design is for one rolling flag; if a second flag is needed later, the class-based pattern scales.
- Frontend (Inertia / Antlers) exposure. API consumers only.
- Route-level access gating. Routes remain accessible to all authenticated users; only the response payload differs.
- A bulk-management CP utility. Per-user toggle plus role assignment is sufficient for v1.
- Force-off semantics. A user with the `beta-tester` role cannot be individually excluded; remove the role to remove access.

## Architecture

```
┌──────────────────────────┐         ┌─────────────────────────┐
│  Statamic CP (Runway)    │ writes  │   users table           │
│  User resource           ├────────▶│   - beta_enabled (bool) │
│  - beta_enabled toggle   │         │                         │
└──────────────────────────┘         │   model_has_roles       │
                                     │   - beta-tester role    │
┌──────────────────────────┐         └────────────┬────────────┘
│  Spatie role admin       │ writes               │
│  (existing flow)         ├──────────────────────┘
└──────────────────────────┘                      │
                                                  ▼
                                   ┌──────────────────────────┐
                                   │  App\Features\BetaFeature│
                                   │  resolve($user) =        │
                                   │    hasRole('beta-tester')│
                                   │    || beta_enabled       │
                                   └────────────┬─────────────┘
                                                │
   ┌─────────────────────┐                      │
   │ UserObserver        │ Feature::forget      │
   │ Role event listener │─────────▶ Pennant cache (database store)
   └─────────────────────┘                      │
                                                ▼
                                ┌──────────────────────────────┐
                                │  DTOs / Builders             │
                                │                              │
                                │  Feature::for($user)         │
                                │    ->when(BetaFeature::class,│
                                │      fn() => $betaShape,     │
                                │      fn() => $standardShape) │
                                └──────────────────────────────┘
```

The resolver:

```
beta active for $user = $user->hasRole('beta-tester') || $user->beta_enabled === true
```

Unauthenticated scope (`Feature::for(null)`) returns `false` per Pennant default → standard payload.

## Components

### 1. Migration: `add_beta_enabled_to_users_table`

- Adds `beta_enabled` boolean column to `users`, default `false`, not null.
- No `down()` method per project convention.

### 2. User model (`app/Models/User.php`)

- Add `'beta_enabled'` to `$fillable`.
- Add `'beta_enabled' => 'boolean'` in `casts()`.

### 3. Spatie role setup

- A `beta-tester` role created via the existing role seeder pattern (idempotent `firstOrCreate`).

### 4. Feature class: `app/Features/BetaFeature.php`

- Class with a single public `resolve(User $user): bool` method.
- Returns `$user->hasRole('beta-tester') || $user->beta_enabled === true`.
- Referenced as `Feature::for($user)->active(BetaFeature::class)` (no magic strings).

### 5. Response branching pattern (no new component)

- Convention only. Branching lives in DTOs (`app/DataTransferObjects`) and Builders (`app/Builders`).
- Controllers and API Resources stay thin and flag-unaware.
- Standard form:

```php
Feature::for($user)->when(
    BetaFeature::class,
    fn () => $this->betaPayload(),
    fn () => $this->standardPayload(),
);
```

### 6. Cache invalidation

- **`UserObserver`** (already exists). Add an `updated` hook: when `$user->wasChanged('beta_enabled')` → `Feature::for($user)->forget(BetaFeature::class)`.
- **Role-assignment listener.** Listens to `Spatie\Permission\Events\RoleAttached` and `RoleDetached`. If the role is `beta-tester`, calls `Feature::for($user)->forget(BetaFeature::class)`. Registered in `EventServiceProvider`.
- **Soft-delete tidy-up** (optional). `UserObserver::deleted` calls `Feature::for($user)->forget(BetaFeature::class)` to keep the `features` table tidy.
- All `forget` calls are wrapped in `try/catch` and log to Sentry. A failed cache invalidation must not block a CP save.

### 7. Runway User resource blueprint

- Add a `toggle` fieldtype with handle `beta_enabled` to the Runway-managed user resource blueprint.
- Placed in an admin-only tab/section.
- Visibility gated to super users (`if: '{{ user:super }}'` or equivalent Runway/Statamic field-visibility mechanism), so non-admins editing a user record cannot see or change it.

### 8. Pennant store

- No changes to `config/pennant.php`. The existing `database` driver is retained.

## Data flow

### Toggling a user via Runway CP

1. Admin opens user in Runway resource, flips the `beta_enabled` switch, saves.
2. Runway persists `users.beta_enabled = true` (or `false`).
3. `UserObserver::updated()` detects the change → `Feature::for($user)->forget(BetaFeature::class)`.
4. Next API request from the user → cache miss → resolver runs → cached → request proceeds.

### Toggling membership via role

1. Admin attaches or detaches the `beta-tester` role on the user.
2. Spatie fires `RoleAttached` / `RoleDetached`.
3. Listener checks the role name. If `beta-tester` → `Feature::for($user)->forget(BetaFeature::class)`. Otherwise early-returns.
4. Cache-miss → resolve → cache flow on next request.

### API request returning a variant payload

1. Authenticated request hits an API route. No beta-specific middleware.
2. Controller delegates to a DTO or Builder.
3. DTO/Builder reaches the variant point and calls `Feature::for($user)->when(BetaFeature::class, ...)`.
4. First call per user: Pennant cache miss → `BetaFeature::resolve($user)` → result cached in `features` table.
5. Subsequent calls: cache hit, no resolver call, no DB query for the flag.
6. The branch returns beta or standard shape. Response serialized and returned.

## Error handling

- **Null user scope.** `Feature::for(null)` returns `false` by Pennant default. Standard payload served. No special handling needed.
- **Unexpected scope type.** `BetaFeature::resolve()` is type-hinted; type errors surface in dev and are reported to Sentry in prod. No silent fallback.
- **Pennant storage failure.** We do not catch. Errors bubble to the global handler and Sentry. A storage failure is a real bug; serving a mismatched payload silently is worse.
- **`Feature::forget` failure inside observer/listener.** Caught, logged to Sentry, swallowed. CP saves and role changes must not fail because of cache invalidation. The next read will repopulate.
- **Role attach/detach for a non-beta role.** Listener early-returns.
- **No-op save (no change to `beta_enabled`).** `wasChanged()` is `false` → observer skips `forget`.
- **Concurrent CP save + in-flight API request.** Worst case: a single in-flight request sees stale `false`. Acceptable — no locking required. Subsequent requests resolve correctly after the observer fires.

## Testing

PEST feature tests against the real `wahlocal_test` database and the database Pennant store. No mocking of Pennant.

### Resolver tests (`tests/Feature/Features/BetaFeatureTest.php`)

- No role and `beta_enabled=false` → `false`.
- `beta-tester` role and `beta_enabled=false` → `true`.
- No role and `beta_enabled=true` → `true`.
- Both sources active → `true`.
- `Feature::for(null)->active(BetaFeature::class)` → `false`.

### Cache invalidation tests (`tests/Feature/Beta/CacheInvalidationTest.php`)

- Resolve, then toggle `beta_enabled` → cache forgotten for that user.
- `assignRole('beta-tester')` → cache forgotten.
- `removeRole('beta-tester')` → cache forgotten.
- Attach or detach a non-beta role → cache untouched.
- Save user without changing `beta_enabled` → no `forget` invoked.

### DTO / Builder branching tests

- For each DTO or Builder that gains a beta variant during implementation, a test pair asserting:
  - In-beta user → beta shape.
  - Out-of-beta user → standard shape.
- Specific DTOs/Builders are out of scope for this design. The implementation plan will enumerate them.

### Blueprint smoke test (optional)

- Load the Runway user blueprint YAML and assert the `beta_enabled` toggle field is present with expected handle and permission rules.

### Migration smoke

- `php artisan migrate:fresh --seed` runs cleanly. The user factory still produces valid users (`beta_enabled` defaults to `false`).

### Comparison test impact

- Beta-gated payload differences must be excluded from production-comparison tests (`--group=comparison`), since production may not have identical flag state. Flag during implementation; do not run beta-affected DTOs/Builders through the comparison suite.

## Open questions

None at this stage. All architectural decisions are locked.
