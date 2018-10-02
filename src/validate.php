<?php

namespace FilippoToso\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Validate inputs for use in API development
 * @method validate
 * @param  array          $rules              Laravel's style validation rules.
 * @param  null|callable  $successCallback    If it's a callable, $successCallback is called with the validated data as parameter.
 *                                            Otherwise, the validated data is returned by validate().
 * @param  null|callable  $failsCallback      If it's a callable, $failsCallback is called with the validator instance as parameter.
 *                                            Otherwise, it checks if the Dingo API development framework is installed.
 *                                            If it's installed, the function throws a ResourceException with the validation errors.
 *                                            Otherwise it returns a Json response with 422 status code and validation errors as payload.
 * @param  array          $messages           The validation messages.
 * @param  mixed          $input              If it's null, request() will be used instead.
 *                                            Otherwise, you can pass a Request object or an array.
 *                                            In any other case, the function throws an exception for invalid input.
 * @return mixed  The function can return: the result of $successCallback(), the result of $failsCallback,
 *                the validated data or a Json response with the validated errors.
 */

function validate(array $rules, callable $successCallback = null, callable $failsCallback = null, $messages = [], $input = null) {

    $validableInputs = collect($rules)->keys()->map(function ($rule) {
        return explode('.', $rule)[0];
    })->unique()->toArray();

    $data = [];

    if (is_null($input)) {
        $input = request()->all();
        $data = request()->only($validableInputs);
    } elseif (is_a($input, Request::class)) {
        $data = $input->only($validableInputs);
    } elseif (is_array($input)) {
        $data = array_only($input, $validableInputs);
    }  else {
        throw new \Exception('Invalid $input parameter!');
    }

    $validator = Validator::make($input, $rules, $messages);

    if ($validator->fails()) {

        if (is_callable($failsCallback)) {
            return $failsCallback($validator);
        }

        if (class_exists(Dingo\Api\Exception\ResourceException::class)) {
            throw new Dingo\Api\Exception\ResourceException('Validation failed.', $validator->errors());
        }

        return response()->json([
            'message' =>'Failed validation.',
            'status_code' => 422,
            'errors' => $validator->errors(),
        ], 422);

    }

    if (is_callable($successCallback)) {
        return $successCallback($data);
    }

    return $data;

}
