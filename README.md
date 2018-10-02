# Validation Function

A simple function that helps validating inputs for use in API development.

## Requirements

- PHP 7.1.3+
- Illumintate HTTP 5.5+
- Illumintate Support 5.5+

## Installing

Use Composer to install it:

```
composer require filippo-toso/validation-support
```

## Function Signature

Here is the signature of the validate() function:

```
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

function validate(array $rules, callable $successCallback = null, callable $failsCallback = null, $messages = [], $input = null)

```

## Using It

Here is the simples example:

```
use function FilippoToso\Validator\validate;
use App\User;
use Illuminate\Http\JsonResponse;

Route::get('/users/search', function() {

    // Call validate() passing only the rules in the first parameter.

    $data = validate([
        'name' => 'nullable|string',
        'email' => 'nullable|string',
        'orderBy' => 'required|in:username,email',
    ]);

    // Validation error
    if (!is_array($data)) {
        return $data;
    }

    // Then use the validated data to get the required response

    $query = User::select('*');

    if (isset($data['name'])) {
        $query->where('name', '=', $data['name']);
    }

    if (isset($data['email'])) {
        $query->where('email', '=', $data['email']);
    }

    $query->orderBy($data['orderBy']);

    return $query->get()->toArray();

});

```

If you prefer, you can achieve the same result using the callable in the second parameter:

```
use function FilippoToso\Validator\validate;
use App\User;

Route::get('/users/search', function() {

    // Call validate() passing the rules in the first parameter and the $successCallback as second.

    return validate([
        'name' => 'nullable|string',
        'email' => 'nullable|string',
        'orderBy' => 'required|in:username,email',
    ], function ($data) {

        // Use the validated data to get the required response.

        $query = User::select('*');

        if (isset($data['name'])) {
            $query->where('name', '=', $data['name']);
        }

        if (isset($data['email'])) {
            $query->where('email', '=', $data['email']);
        }

        $query->orderBy($data['orderBy']);

        // The return of this closure will be the validate() return value.

        return $query->get()->toArray();

    });

});

```

The third parameter is also a callback that it's called if the validator fails.
In this case the instance of the validator is passed as parameter.
You can use it, for instance, to generate a custom error payload:

```
use function FilippoToso\Validator\validate;
use App\User;

Route::get('/users/search', function() {

    // Call validate() passing the rules in the first parameter and the $successCallback as second.

    return validate([
        'name' => 'nullable|string',
        'email' => 'nullable|string',
        'orderBy' => 'required|in:username,email',
    ], function ($data) {

        // Use the validated data to get the required response.

        $query = User::select('*');

        if (isset($data['name'])) {
            $query->where('name', '=', $data['name']);
        }

        if (isset($data['email'])) {
            $query->where('email', '=', $data['email']);
        }

        $query->orderBy($data['orderBy']);

        // The return of this closure will be the validate() return value.

        return $query->get()->toArray();

    }, function ($validator) {

        // This is exactly what happens if you pass null as $failsCallback and
        // the Dingo framework is not installed.

        return response()->json([
            'message' =>'Faild validation.',
            'status_code' => 422,
            'errors' => $validator->errors(),
        ], 422);

    });

});

```

The forth parameter contains the validator's messages (like the third parameter in Validator::make() or the controller's messages() method).

The last parameter can contain null, a Request object or an array. In the first case the current request() will be used otherwise the content passed will be validated. If the input parameter doesn't contain a valid value, an exception will be thrown.
