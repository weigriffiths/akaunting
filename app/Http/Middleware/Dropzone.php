<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;

class Dropzone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (($request->method() != 'POST') && ($request->method() != 'PATCH')) {
            return $next($request);
        }

        $multiple = false;

        foreach ($request->all() as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $files = [];
            $uploaded = [];

            foreach ($value as $index => $parameter) {
                // single file uploaded..
                if (!is_array($parameter) && !$multiple) {
                    if (!Arr::has($value, 'dropzone')) {
                        continue;
                    }

                    $request->request->set('uploaded_' . $key, $value);

                    unset($request[$key]);
                    break;
                }

                // multiple file uploaded..
                if (!Arr::has($parameter, 'dropzone')) {
                    $files[] = $parameter;

                    continue;
                }

                $multiple = true;
                $uploaded[] = $parameter;
            }

            if ($multiple && $uploaded) {
                $request->request->set('uploaded_' . $key, $uploaded);
                $request->request->set($key, $files);
            }
        }

        return $next($request);
    }
}