<?php

namespace App\Http\Middleware;

use App\Models\Book;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBookOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $book = $request->route('book');

        if ($book instanceof Book) {
            if ($book->user_id !== $request->user()->id) {
                abort(403, 'You do not have permission to access this book.');
            }
        }

        return $next($request);
    }
}
