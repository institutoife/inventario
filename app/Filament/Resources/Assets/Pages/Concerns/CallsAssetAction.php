<?php

namespace App\Filament\Resources\Assets\Pages\Concerns;

use App\Actions\Assets\SaveAsset;
use App\Models\Asset;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait CallsAssetAction
{
    protected function persistAsset(array $data, ?Asset $asset = null, ?string $token = null): Asset
    {
        try {
            return app(SaveAsset::class)->execute(auth()->user(), $data, $asset, $token);
        } catch (ValidationException $exception) {
            $errors = [];
            foreach ($exception->errors() as $field => $messages) {
                $errors['data.'.$field] = $messages;
            }
            throw ValidationException::withMessages($errors);
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) {
                throw $exception;
            }
            throw ValidationException::withMessages(['data.code' => $exception->getMessage()]);
        }
    }
}
