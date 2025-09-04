<?php

namespace App\Http\Controllers\API\V1\Navs;

use Statamic\Facades\GlobalSet;
use App\Http\Controllers\Controller;

abstract class BaseNavController extends Controller
{
    /**
     * Get the global set handle for the navigation
     */
    abstract protected function getGlobalSetHandle(): string;

    /**
     * Process the navigation data before returning
     */
    abstract protected function processNavigationData(array $data): array;

    /**
     * Get the error message when global set is not found
     */
    protected function getNotFoundMessage(): string
    {
        return ucfirst(str_replace('_', ' ', $this->getGlobalSetHandle())) . ' global set not found';
    }

    /**
     * Fetch and process navigation data
     */
    protected function getNavigationData()
    {
        $globalSet = GlobalSet::findByHandle($this->getGlobalSetHandle());

        if (! $globalSet) {
            return response()->json([
                'error' => $this->getNotFoundMessage(),
            ], 404);
        }

        // Get the variables (actual content) for the default site
        $variables = $globalSet->inDefaultSite();

        if (! $variables) {
            return response()->json([
                'error' => 'No navigation data found for default site',
            ], 404);
        }

        // Get the navigation data
        $data = $variables->data()->all();

        // Process and return the data
        return response()->json($this->processNavigationData($data));
    }

    /**
     * Filter items by enabled status
     */
    protected function filterEnabledItems($items): array
    {
        return collect($items)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->values()
            ->all();
    }

    /**
     * Filter nested dropdown links by enabled status
     */
    protected function filterNestedDropdownLinks(array $item): array
    {
        if (isset($item['dropdown_links']) && is_array($item['dropdown_links'])) {
            $item['dropdown_links'] = $this->filterEnabledItems($item['dropdown_links']);
        }

        return $item;
    }
}
