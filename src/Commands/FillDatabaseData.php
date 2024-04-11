<?php

namespace Kerigard\LaravelData\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Kerigard\LaravelData\Contracts\MustFillData;
use Kerigard\LaravelData\Data;
use Kerigard\LaravelData\DataManager;
use Kerigard\LaravelData\Events\Dispatcher;
use Kerigard\LaravelData\Models\DataModel;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(name: 'db:data')]
class FillDatabaseData extends Command
{
    protected $signature = 'db:data';

    protected $description = 'Fill the database with required data';

    public function handle(): void
    {
        $models = $this->getModels();

        if ($models->isEmpty()) {
            $this->components->info('No data to fill out.');

            return;
        }

        $this->components->info('Running filling the database.');

        $models->each(function ($model, $key) {
            if (is_callable($model)) {
                $callback = $model;
                $model = new $key();
                $data = $callback($model);
            } elseif (is_array($model)) {
                $callback = $model[1];
                $model = DataModel::bind($model[0]);
                $data = $callback($model);
            } else {
                $data = $model->data();
            }

            $this->components->task(
                $model instanceof DataModel ? $model->getTable() : get_class($model),
                fn () => $this->fill($model, $data)
            );
        });

        $this->newLine();
    }

    protected function getModels(): Collection
    {
        return collect(File::allFiles(app_path()))
            ->map(function (SplFileInfo $item) {
                $path = $item->getRelativePathName();
                $class = sprintf(
                    '\%s%s',
                    app()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );

                return $class;
            })
            ->filter(function (string $class) {
                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);

                    return !$reflection->isAbstract() &&
                        $reflection->isSubclassOf(Model::class) &&
                        $reflection->implementsInterface(MustFillData::class);
                }

                return false;
            })
            ->map(fn (string $class) => new $class())
            ->merge(DataManager::$models)
            ->merge(DataManager::$tables);
    }

    protected function fill(Model $model, Data $data): void
    {
        $softDeletable = $this->isSoftDeletableModel($model);

        $callback = fn () => Model::unguarded(fn () => $data->delete
            ? $this->sync($model, $data->rows, $softDeletable)
            : $this->save($model, $data->rows, $softDeletable));

        if ($data->allowEvents === true) {
            $callback();

            return;
        }

        $this->withoutModelEvents($model, $callback, $data->allowEvents ?: []);
    }

    protected function sync(Model $model, Collection $rows, bool $softDeletable): void
    {
        $model
            ->query()
            ->when(
                is_null($model->getKeyName()),
                fn (Builder $query) => $query->whereNot(function (Builder $query) use ($rows) {
                    $rows->each(fn (array $row) => $query->orWhere(function (Builder $query) use ($row) {
                        foreach ($row as $key => $value) {
                            $query->where($key, $value);
                        }
                    }));
                }),
                fn (Builder $query) => $query->whereNotIn($model->getKeyName(), $rows->pluck($model->getKeyName())),
            )
            ->when(
                $softDeletable,
                fn (Builder $query) => $query->withTrashed()->forceDelete(),
                fn (Builder $query) => $query->delete()
            );

        $this->save($model, $rows, $softDeletable);
    }

    protected function save(Model $model, Collection $rows, bool $softDeletable): void
    {
        $rows->each(
            fn (array $row) => $model
                ->query()
                ->when($softDeletable, fn (Builder $query) => $query->withTrashed())
                ->when(
                    is_null($model->getKeyName()),
                    fn (Builder $query) => $query->firstOrNew($row),
                    fn (Builder $query) => $query->firstOrNew([$model->getKeyName() => $row[$model->getKeyName()]]),
                )
                ->fill($row)
                ->save()
        );
    }

    protected function withoutModelEvents(Model $model, callable $callback, array $allowEvents): void
    {
        $dispatcher = $model->getEventDispatcher();

        if ($dispatcher) {
            $model->setEventDispatcher(new Dispatcher($dispatcher, $allowEvents));
        }

        try {
            $callback();
        } finally {
            if ($dispatcher) {
                $model->setEventDispatcher($dispatcher);
            }
        }
    }

    protected function isSoftDeletableModel(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(get_class($model)));
    }
}
