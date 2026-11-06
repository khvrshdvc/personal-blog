<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_mehmon_dashboardga_kira_olmaydi(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_login_sahifasi_ochiladi(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_togri_malumot_bilan_kiriladi(): void
    {
        $user = User::factory()->create(['password' => 'sir-parol']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'sir-parol'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_notogri_parol_rad_etiladi(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'xato'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_cheklangan(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'xato']);
        }

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'xato'])
            ->assertStatus(429);
    }

    public function test_chiqish_ishlaydi(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_kirgan_foydalanuvchi_loginga_otmaydi(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/login')
            ->assertRedirect(route('admin.dashboard'));
    }
}
