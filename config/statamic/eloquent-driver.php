<?php

use Statamic\Eloquent\Assets\Asset;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Eloquent\Forms\FormModel;
use Statamic\Eloquent\Assets\AssetModel;
use Statamic\Eloquent\Tokens\TokenModel;
use Statamic\Eloquent\Structures\NavTree;
use Statamic\Eloquent\Structures\NavModel;
use Statamic\Eloquent\Fields\FieldsetModel;
use Statamic\Eloquent\Structures\TreeModel;
use Statamic\Eloquent\Taxonomies\TermModel;
use Statamic\Eloquent\Fields\BlueprintModel;
use Statamic\Eloquent\Forms\SubmissionModel;
use Statamic\Eloquent\Entries\UuidEntryModel;
use Statamic\Eloquent\Globals\GlobalSetModel;
use Statamic\Eloquent\Globals\VariablesModel;
use Statamic\Eloquent\Revisions\RevisionModel;
use Statamic\Eloquent\Taxonomies\TaxonomyModel;
use Statamic\Eloquent\Structures\CollectionTree;
use Statamic\Eloquent\Assets\AssetContainerModel;
use Statamic\Eloquent\Collections\CollectionModel;

return [
    'connection' => env('STATAMIC_ELOQUENT_CONNECTION', ''),
    'table_prefix' => env('STATAMIC_ELOQUENT_PREFIX', ''),

    'asset_containers' => [
        'driver' => 'file',
        'model' => AssetContainerModel::class,
    ],

    'assets' => [
        'driver' => 'eloquent',
        'model' => AssetModel::class,
        'asset' => Asset::class,
    ],

    'blueprints' => [
        'driver' => 'file',
        'blueprint_model' => BlueprintModel::class,
        'fieldset_model' => FieldsetModel::class,
    ],

    'collections' => [
        'driver' => 'file',
        'model' => CollectionModel::class,
        'update_entry_order_queue' => 'default',
        'update_entry_order_connection' => 'default',
    ],

    'collection_trees' => [
        'driver' => 'eloquent',
        'model' => TreeModel::class,
        'tree' => CollectionTree::class,
    ],

    'entries' => [
        'driver' => 'eloquent',
        'model' => UuidEntryModel::class,
        'entry' => Entry::class,
    ],

    'forms' => [
        'driver' => 'file',
        'model' => FormModel::class,
    ],

    'form_submissions' => [
        'driver' => 'eloquent',
        'model' => SubmissionModel::class,
    ],

    'global_sets' => [
        'driver' => 'file',
        'model' => GlobalSetModel::class,
    ],

    'global_set_variables' => [
        'driver' => 'eloquent',
        'model' => VariablesModel::class,
    ],

    'navigations' => [
        'driver' => 'file',
        'model' => NavModel::class,
    ],

    'navigation_trees' => [
        'driver' => 'eloquent',
        'model' => TreeModel::class,
        'tree' => NavTree::class,
    ],

    'revisions' => [
        'driver' => 'file',
        'model' => RevisionModel::class,
    ],

    'taxonomies' => [
        'driver' => 'file',
        'model' => TaxonomyModel::class,
    ],

    'terms' => [
        'driver' => 'eloquent',
        'model' => TermModel::class,
    ],

    'tokens' => [
        'driver' => 'eloquent',
        'model' => TokenModel::class,
    ],
];
