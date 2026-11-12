<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function reservationFor(User $user, Room $room): Reservation
    {
        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'check_in' => today()->addDays(2),
            'check_out' => today()->addDays(4),
            'total_price' => 200,
            'status' => 'confirmed',
        ]);
    }

    private function room(): Room
    {
        return Room::firstOrCreate(['room_number' => '101'], ['type' => 'Doble', 'price_per_night' => 100, 'status' => 'available', 'capacity' => 2]);
    }

    public function test_user_only_sees_own_reservations_in_dashboard(): void
    {
        $room = $this->room();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $mine = $this->reservationFor($a, $room);
        $theirs = $this->reservationFor($b, $room);

        $reservations = $this->actingAs($a)->get('/dashboard')->assertOk()->viewData('myReservations');

        $this->assertTrue($reservations->contains('id', $mine->id));
        $this->assertFalse($reservations->contains('id', $theirs->id));
    }

    public function test_user_cannot_download_or_cancel_someone_elses_reservation(): void
    {
        $room = $this->room();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $theirs = $this->reservationFor($b, $room);

        $this->actingAs($a)->get("/reservations/{$theirs->id}/pdf")->assertNotFound();
        $this->actingAs($a)->patch("/reservations/{$theirs->id}/cancel")->assertNotFound();
        $this->assertSame('confirmed', $theirs->fresh()->status);
    }

    public function test_user_can_cancel_own_reservation(): void
    {
        $a = User::factory()->create();
        $mine = $this->reservationFor($a, $this->room());

        $this->actingAs($a)->patch("/reservations/{$mine->id}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $mine->fresh()->status);
    }

    public function test_admin_can_access_any_reservation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $theirs = $this->reservationFor(User::factory()->create(), $this->room());

        $this->actingAs($admin)->patch("/reservations/{$theirs->id}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $theirs->fresh()->status);
    }

    private function reservationAt(User $user, int $startsInDays, int $nights = 2): Reservation
    {
        return Reservation::create([
            'user_id' => $user->id,
            'room_id' => $this->room()->id,
            'check_in' => today()->addDays($startsInDays),
            'check_out' => today()->addDays($startsInDays + $nights),
            'total_price' => 200,
            'status' => 'confirmed',
        ]);
    }

    public function test_cancellation_stores_reason_author_and_sends_mail(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $a = User::factory()->create();
        $r = $this->reservationAt($a, 5);

        $this->actingAs($a)->patch("/reservations/{$r->id}/cancel", ['reason' => 'Cambio de planes'])->assertSessionHas('success');

        $r->refresh();
        $this->assertSame('cancelled', $r->status);
        $this->assertSame('Cambio de planes', $r->cancel_reason);
        $this->assertSame($a->id, $r->cancelled_by);
        $this->assertNotNull($r->cancelled_at);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ReservationCancelled::class);
    }

    public function test_guest_cannot_cancel_after_deadline(): void
    {
        $a = User::factory()->create();
        $r = $this->reservationAt($a, 0);

        $this->actingAs($a)->patch("/reservations/{$r->id}/cancel")->assertSessionHas('error');
        $this->assertSame('confirmed', $r->fresh()->status);
    }

    public function test_cancelling_twice_is_rejected_without_overwriting(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $a = User::factory()->create();
        $r = $this->reservationAt($a, 5);

        $this->actingAs($a)->patch("/reservations/{$r->id}/cancel", ['reason' => 'Cambio de planes']);
        $this->actingAs($a)->patch("/reservations/{$r->id}/cancel", ['reason' => 'Otro'])->assertSessionHas('error');

        $this->assertSame('Cambio de planes', $r->fresh()->cancel_reason);
        \Illuminate\Support\Facades\Mail::assertSentCount(1);
    }

    public function test_admin_can_cancel_inside_deadline_but_not_finished_stay(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $guest = User::factory()->create();

        $soon = $this->reservationAt($guest, 0);
        $this->actingAs($admin)->patch("/reservations/{$soon->id}/cancel")->assertSessionHas('success');
        $this->assertSame('cancelled', $soon->fresh()->status);

        $past = $this->reservationAt($guest, -10);
        $this->actingAs($admin)->patch("/reservations/{$past->id}/cancel")->assertSessionHas('error');
        $this->assertSame('confirmed', $past->fresh()->status);
    }

    public function test_cancelled_room_can_be_booked_again(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $r = $this->reservationAt($a, 5);

        $this->actingAs($a)->patch("/reservations/{$r->id}/cancel");

        $this->actingAs($b)->post('/reservations', [
            'room_id' => $r->room_id,
            'check_in' => $r->check_in->toDateString(),
            'check_out' => $r->check_out->toDateString(),
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $b->reservations()->count());
    }
}