<?php

namespace Tests\Feature\Admin;

use App\Models\DamageReport;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'            => 'Admin Biro Sarpras',
            'email'           => 'admin@cava.ac.id',
            'role'            => 'admin',
            'status'          => 'active',
            'identity_number' => 'ADM-001',
        ]);
        $this->admin->syncRoles(['admin']);

        $this->facility = Facility::firstOrCreate([
            'code'           => 'AUD-H01',
        ], [
            'name'           => 'Auditorium B.J. Habibie',
            'category'       => 'auditorium',
            'building'       => 'Gedung Rektorat',
            'floor_location' => 'Lantai 1',
            'capacity'       => 450,
            'equipment'      => ['AC Central', 'Sound System'],
            'description'    => 'Auditorium utama universitas.',
            'status'         => 'aktif',
        ]);
    }

    public function test_admin_dashboard_renders_with_dynamic_metrics(): void
    {
        // Buat data transaksi dan tiket kerusakan
        Reservation::create([
            'ticket_code'        => 'TKT-TEST-001',
            'user_id'            => $this->admin->id,
            'facility_id'        => $this->facility->id,
            'reservation_date'   => now()->toDateString(),
            'start_time'         => '08:00:00',
            'end_time'           => '10:00:00',
            'total_slots'        => 4,
            'purpose'            => 'Seminar Nasional Teknologi',
            'participants_count' => 150,
            'status'             => 'approved',
        ]);

        DamageReport::create([
            'report_code' => 'RPT-TEST-001',
            'user_id'     => $this->admin->id,
            'facility_id' => $this->facility->id,
            'category'    => 'AC & Pendingin Ruang',
            'description' => 'AC blower unit 2 mati',
            'status'      => 'baru',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Auditorium B.J. Habibie');
        $response->assertSee('Dasbor Super Admin');
    }

    public function test_export_report_page_renders_with_default_date_filters(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.export-report'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Okupansi');
        $response->assertSee('Cetak Dokumen PDF Resmi');
        $response->assertSee('Ekspor Excel / CSV');
    }

    public function test_export_report_handles_reversed_date_filters_gracefully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.export-report', [
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date'   => now()->subDays(5)->toDateString(),
        ]));

        $response->assertStatus(200);
    }

    public function test_can_download_reservations_pdf_report(): void
    {
        Reservation::create([
            'ticket_code'        => 'TKT-TEST-PDF',
            'user_id'            => $this->admin->id,
            'facility_id'        => $this->facility->id,
            'reservation_date'   => now()->toDateString(),
            'start_time'         => '09:00:00',
            'end_time'           => '12:00:00',
            'total_slots'        => 6,
            'purpose'            => 'Rapat Senat Akademik',
            'participants_count' => 50,
            'status'             => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.export.reservations.pdf', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_can_download_reservations_excel_report(): void
    {
        Reservation::create([
            'ticket_code'        => 'TKT-TEST-XLSX',
            'user_id'            => $this->admin->id,
            'facility_id'        => $this->facility->id,
            'reservation_date'   => now()->toDateString(),
            'start_time'         => '08:00:00',
            'end_time'           => '11:00:00',
            'total_slots'        => 6,
            'purpose'            => 'Kuliah Tamu Industri',
            'participants_count' => 100,
            'status'             => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.export.reservations.excel', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_can_download_damage_reports_pdf(): void
    {
        DamageReport::create([
            'report_code' => 'RPT-TEST-PDF',
            'user_id'     => $this->admin->id,
            'facility_id' => $this->facility->id,
            'category'    => 'Kelistrikan / Stop Kontak',
            'description' => 'Konsleting stop kontak panggung',
            'status'      => 'selesai',
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.export.damage-reports.pdf', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_can_download_damage_reports_excel(): void
    {
        DamageReport::create([
            'report_code' => 'RPT-TEST-XLSX',
            'user_id'     => $this->admin->id,
            'facility_id' => $this->facility->id,
            'category'    => 'Proyektor & Audio',
            'description' => 'Kabel HDMI longgar di panggung',
            'status'      => 'diproses',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.export.damage-reports.excel', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_export_mockup_alias_routes_work(): void
    {
        $responseExcel = $this->actingAs($this->admin)->get(route('admin.reports.export-excel'));
        $responseExcel->assertStatus(200);

        $responsePdf = $this->actingAs($this->admin)->get(route('admin.reports.export-pdf'));
        $responsePdf->assertStatus(200);
    }
}
