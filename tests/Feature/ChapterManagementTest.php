<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\ChapterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_create_chapter(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('chapters.store', $this->book));

        $response->assertStatus(201);
        $this->assertDatabaseHas('chapters', [
            'book_id' => $this->book->id,
            'title' => 'Chapter 1',
            'order_number' => 1,
        ]);
    }

    public function test_chapters_get_sequential_order(): void
    {
        $this->actingAs($this->user)->postJson(route('chapters.store', $this->book));
        $this->actingAs($this->user)->postJson(route('chapters.store', $this->book));
        $this->actingAs($this->user)->postJson(route('chapters.store', $this->book));

        $chapters = $this->book->chapters()->orderBy('order_number')->get();
        $this->assertEquals([1, 2, 3], $chapters->pluck('order_number')->toArray());
    }

    public function test_user_can_save_chapter_content(): void
    {
        $chapter = Chapter::factory()->create([
            'book_id' => $this->book->id,
            'order_number' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('chapters.content', [$this->book, $chapter]), [
                'content_delta' => ['ops' => [['insert' => "Hello world\n"]]],
                'content_html' => '<p>Hello world</p>',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'word_count', 'saved_at']);

        $chapter->refresh();
        $this->assertEquals(2, $chapter->word_count);
    }

    public function test_word_count_calculation(): void
    {
        $service = new ChapterService();

        $this->assertEquals(0, $service->calculateWordCount(''));
        $this->assertEquals(2, $service->calculateWordCount('<p>Hello world</p>'));
        $this->assertEquals(5, $service->calculateWordCount('<p>The <strong>quick</strong> brown fox jumps</p>'));
        $this->assertEquals(3, $service->calculateWordCount('<p>One   two   three</p>'));
    }

    public function test_chapter_deletion_reorders_remaining(): void
    {
        $ch1 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 1]);
        $ch2 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 2]);
        $ch3 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 3]);

        $this->actingAs($this->user)
            ->deleteJson(route('chapters.destroy', [$this->book, $ch2]));

        $this->assertDatabaseMissing('chapters', ['id' => $ch2->id]);
        $this->assertEquals(1, $ch1->fresh()->order_number);
        $this->assertEquals(2, $ch3->fresh()->order_number);
    }

    public function test_chapter_reorder(): void
    {
        $ch1 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 1]);
        $ch2 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 2]);
        $ch3 = Chapter::factory()->create(['book_id' => $this->book->id, 'order_number' => 3]);

        $response = $this->actingAs($this->user)
            ->putJson(route('chapters.reorder', $this->book), [
                'order' => [$ch3->id, $ch1->id, $ch2->id],
            ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $ch3->fresh()->order_number);
        $this->assertEquals(2, $ch1->fresh()->order_number);
        $this->assertEquals(3, $ch2->fresh()->order_number);
    }
}
