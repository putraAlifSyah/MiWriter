<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_book(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => 'My Novel',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'My Novel',
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
    }

    public function test_book_title_is_required(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_book_title_max_200_chars(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title' => str_repeat('a', 201),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_user_can_view_their_books(): void
    {
        Book::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertStatus(200);
    }

    public function test_books_ordered_by_updated_at_desc(): void
    {
        $book1 = Book::factory()->create(['user_id' => $this->user->id, 'updated_at' => now()->subDays(2)]);
        $book2 = Book::factory()->create(['user_id' => $this->user->id, 'updated_at' => now()]);
        $book3 = Book::factory()->create(['user_id' => $this->user->id, 'updated_at' => now()->subDay()]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);

        // The most recently updated book should appear first
        $response->assertSeeInOrder([$book2->title, $book3->title, $book1->title]);
    }

    public function test_user_can_update_book(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('books.update', $book), [
            'title' => 'Updated Title',
            'synopsis' => 'A great story.',
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'synopsis' => 'A great story.',
            'status' => 'in_progress',
        ]);
    }

    public function test_user_can_delete_book(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);
        Chapter::factory()->create(['book_id' => $book->id, 'order_number' => 1]);

        $response = $this->actingAs($this->user)->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    public function test_user_cannot_access_other_users_book(): void
    {
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);
        $chapter = Chapter::factory()->create(['book_id' => $book->id, 'order_number' => 1]);

        $response = $this->actingAs($this->user)->get(route('chapters.show', [$book, $chapter]));

        $response->assertStatus(403);
    }
}
