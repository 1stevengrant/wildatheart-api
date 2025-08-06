# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Wild at Heart Local is a Laravel-based platform for connecting Wild at Heart community members to events worldwide via a map-based interface. It includes a headless CMS that powers the main Wild at Heart website.

**Core Technologies:**
- **Backend**: Laravel 11 with PHP 8.3
- **Frontend**: React with TypeScript via Inertia.js
- **Styling**: Tailwind CSS
- **State Management**: Recoil (for mapping/filtering)
- **Database**: MySQL 8.0
- **CMS**: Statamic v5
- **Testing**: PEST Framework
- **Queue**: Redis/Horizon

## Development Commands

### Setup and Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Development Server
```bash
php artisan serve      # Backend server
npm run dev           # Frontend development server with hot reload
```

### Building
```bash
npm run build         # Production build (includes TypeScript compilation)
npm run lint          # ESLint check
npm run lint:fix      # ESLint auto-fix
npm run format        # Format with Prettier + ESLint
```

### Testing
```bash
php artisan test      # Run PEST tests
./vendor/bin/pest     # Alternative test command
```

### Code Quality
```bash
./vendor/bin/pint     # Laravel Pint code formatting
```

### Database Operations
```bash
php artisan migrate:fresh --seed    # Reset database with seeds
php artisan db:production-sync      # Sync production database (requires SSH config)
```

### Cache Management
```bash
php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear && php artisan event:clear
```

## Architecture Overview

### Domain Model
- **Events**: Core entity representing gatherings (fires, retreats, experiences)
- **EventTypes**: Categories defining event characteristics and requirements
- **Users**: Auth0-integrated user management with roles (admin, host, attendee)
- **Attendees**: Many-to-many relationship between users and events
- **Addresses**: Location data for events

### Key Design Patterns
- **Action Classes**: Complex business logic in `app/Actions/` (using lorisleiva/laravel-actions)
- **Data Transfer Objects**: API and form data structures in `app/DataTransferObjects/`
- **Builders**: Query builders for complex data retrieval in `app/Builders/`
- **Observers**: Model lifecycle hooks in `app/Observers/`
- **Jobs**: Asynchronous processing with Redis/Horizon

### Frontend Architecture
- **Pages**: Inertia.js pages in `resources/js/Pages/`
- **Components**: Reusable React components in `resources/js/Components/`
- **Hooks**: Custom React hooks in `resources/js/Hooks/`
- **State**: Recoil atoms in `resources/js/store/`
- **Layout System**: Multiple layout components (App, Sidebar, HostDashboard)

### Integration Layers
- **Auth0**: Authentication and user management
- **Statamic CMS**: Content management via Runway addon
- **Mapbox**: Interactive maps for event discovery
- **AWS S3**: Asset storage
- **Mailgun/Postmark**: Email delivery
- **Sentry**: Error tracking
- **Slack**: Admin notifications

## Testing Configuration

- **Test Database**: `wahlocal_test`
- **Framework**: PEST with Laravel plugin
- **Memory Requirement**: 256MB+ for Herd users
- **Environment**: Copy `.env.testing.example` to `.env.testing`

## Code Style and Conventions

### PHP (Laravel Pint)
- Laravel preset with custom rules
- Concat spacing: one space
- Ordered imports by length
- Modern type casting

### TypeScript/React
- 4-space indentation
- ESLint + Prettier configuration
- Strict TypeScript compilation

### File Organization
- Actions: Complex business logic
- Services: External API integrations
- Support: Helper functions and utilities
- Traits: Shared model behaviors

## Branch Strategy

- **Feature branches** → `staging` → `prod`
- **Preview deployments**: Add `[wah]` to PR title for temporary staging URL
- **Auto-deployment**: GitHub Actions with Laravel Forge

## Key Dependencies to Know

### Backend
- **spatie/laravel-activitylog**: Model change tracking
- **spatie/laravel-permission**: Role-based access control
- **statamic/cms**: Headless CMS
- **laravel/horizon**: Queue monitoring
- **auth0/auth0-php**: Authentication provider

### Frontend
- **@inertiajs/react**: SPA routing
- **recoil**: State management
- **react-map-gl**: Mapbox integration
- **@mantine/core**: Component library (date picker)
- **react-international-phone**: Phone number handling

## Environment-Specific Notes

- **Local Development**: Uses Laravel Valet/Herd recommended
- **Database Sync**: Production sync available via SSH tunnel
- **Asset Storage**: S3 integration required for media
- **Queue Workers**: Horizon for job processing
- **Error Tracking**: Sentry integration in production