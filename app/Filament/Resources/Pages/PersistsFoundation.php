<?php

namespace App\Filament\Resources\Pages;

use App\Actions\SaveFoundation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait PersistsFoundation
{
    protected function persistFoundation(array $data, ?Model $record = null, ?string $token = null): Model
    {
        try {
            return app(SaveFoundation::class)->execute(static::getResource()::$resourceKey, $data, $record, $token);
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors['data.'.$field] = $messages;
            }
            throw ValidationException::withMessages($errors);
        } catch (HttpException $e) {
            if (! in_array($e->getStatusCode(), [409, 422])) {
                throw $e;
            }
            throw ValidationException::withMessages(['data.reason' => $e->getMessage()]);
        }
    }
}
