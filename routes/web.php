<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\LandingPageController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KalenderAkademikController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\OrganisasiController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\TenagaKependidikanController;
use App\Http\Controllers\Admin\PrestasiController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\UserController;


Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::post('/contact-message', [LandingPageController::class, 'storeContactMessage'])->name('contact.store');

// User Dashboard Route
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:user'])->name('user.dashboard');


Route::get('admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

// ─── Profil Sekolah ───────────────────────────────
Route::prefix('profil')->name('profil.')->group(function () {
    Route::get('/sejarah',         [LandingPageController::class, 'sejarah'])       ->name('sejarah');
    Route::get('/visi-misi',       [LandingPageController::class, 'visiMisi'])       ->name('visi-misi');
    Route::get('/tenaga-pendidik', [LandingPageController::class, 'tenagaPendidik'])->name('tenaga-pendidik');
});

// ─── Informasi ────────────────────────────────────
Route::prefix('informasi')->name('informasi.')->group(function () {
    Route::get('/berita',              [LandingPageController::class, 'berita'])             ->name('berita');
    Route::get('/berita/{slug}',       [LandingPageController::class, 'beritaDetail'])       ->name('berita.detail');
    Route::get('/artikel',             [LandingPageController::class, 'artikel'])            ->name('artikel');
    Route::get('/artikel/{slug}',      [LandingPageController::class, 'artikelDetail'])      ->name('artikel.detail');
    Route::get('/kalender-akademik',   [LandingPageController::class, 'kalenderAkademik'])   ->name('kalender-akademik');
});

// ─── Prestasi — menu mandiri ──────────────────────
Route::get('/prestasi', [LandingPageController::class, 'prestasi'])->name('prestasi');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {

    // Users Routes
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Siswa Routes
    Route::get('/siswa', [SiswaController::class, 'index'])->name('admin.siswa.index');
    Route::get('/siswa/create', [SiswaController::class, 'create'])->name('admin.siswa.create');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('admin.siswa.store');
    Route::get('/siswa/{siswa}', [SiswaController::class, 'show'])->name('admin.siswa.show');
    Route::get('/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('admin.siswa.edit');
    Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('admin.siswa.update');
    Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('admin.siswa.destroy');

    // Guru Routes
    Route::get('/guru', [GuruController::class, 'index'])->name('admin.guru.index');
    Route::get('/guru/create', [GuruController::class, 'create'])->name('admin.guru.create');
    Route::post('/guru', [GuruController::class, 'store'])->name('admin.guru.store');
    Route::get('/guru/{guru}', [GuruController::class, 'show'])->name('admin.guru.show');
    Route::get('/guru/{guru}/edit', [GuruController::class, 'edit'])->name('admin.guru.edit');
    Route::put('/guru/{guru}', [GuruController::class, 'update'])->name('admin.guru.update');
    Route::delete('/guru/{guru}', [GuruController::class, 'destroy'])->name('admin.guru.destroy');

    // Kelas Routes
    Route::get('/kelas', [KelasController::class, 'index'])->name('admin.kelas.index');
    Route::get('/kelas/create', [KelasController::class, 'create'])->name('admin.kelas.create');
    Route::post('/kelas', [KelasController::class, 'store'])->name('admin.kelas.store');
    Route::get('/kelas/{kelas}', [KelasController::class, 'show'])->name('admin.kelas.show');
    Route::get('/kelas/{kelas}/edit', [KelasController::class, 'edit'])->name('admin.kelas.edit');
    Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->name('admin.kelas.update');
    Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->name('admin.kelas.destroy');

    // Mata Pelajaran Routes
    Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('admin.mata-pelajaran.index');
    Route::get('/mata-pelajaran/create', [MataPelajaranController::class, 'create'])->name('admin.mata-pelajaran.create');
    Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('admin.mata-pelajaran.store');
    Route::get('/mata-pelajaran/{mapel}', [MataPelajaranController::class, 'show'])->name('admin.mata-pelajaran.show');
    Route::get('/mata-pelajaran/{mapel}/edit', [MataPelajaranController::class, 'edit'])->name('admin.mata-pelajaran.edit');
    Route::put('/mata-pelajaran/{mapel}', [MataPelajaranController::class, 'update'])->name('admin.mata-pelajaran.update');
    Route::delete('/mata-pelajaran/{mapel}', [MataPelajaranController::class, 'destroy'])->name('admin.mata-pelajaran.destroy');

    // Kalender Akademik Routes
    Route::get('/kalender-akademik', [KalenderAkademikController::class, 'index'])->name('admin.kalender-akademik.index');
    Route::get('/kalender-akademik/create', [KalenderAkademikController::class, 'create'])->name('admin.kalender-akademik.create');
    Route::post('/kalender-akademik', [KalenderAkademikController::class, 'store'])->name('admin.kalender-akademik.store');
    Route::get('/kalender-akademik/{kalenderAkademik}', [KalenderAkademikController::class, 'show'])->name('admin.kalender-akademik.show');
    Route::get('/kalender-akademik/{kalenderAkademik}/edit', [KalenderAkademikController::class, 'edit'])->name('admin.kalender-akademik.edit');
    Route::put('/kalender-akademik/{kalenderAkademik}', [KalenderAkademikController::class, 'update'])->name('admin.kalender-akademik.update');
    Route::delete('/kalender-akademik/{kalenderAkademik}', [KalenderAkademikController::class, 'destroy'])->name('admin.kalender-akademik.destroy');

    // Tahun Ajaran Routes
    Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('admin.tahun-ajaran.index');
    Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->name('admin.tahun-ajaran.create');
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('admin.tahun-ajaran.store');
    Route::get('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'show'])->name('admin.tahun-ajaran.show');
    Route::get('/tahun-ajaran/{tahunAjaran}/edit', [TahunAjaranController::class, 'edit'])->name('admin.tahun-ajaran.edit');
    Route::put('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('admin.tahun-ajaran.update');
    Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('admin.tahun-ajaran.destroy');
    Route::post('/tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('admin.tahun-ajaran.activate');

    // Tenaga Kependidikan Routes
    Route::get('/tenaga-kependidikan', [TenagaKependidikanController::class, 'index'])->name('admin.tenaga-kependidikan.index');
    Route::get('/tenaga-kependidikan/create', [TenagaKependidikanController::class, 'create'])->name('admin.tenaga-kependidikan.create');
    Route::post('/tenaga-kependidikan', [TenagaKependidikanController::class, 'store'])->name('admin.tenaga-kependidikan.store');
    Route::get('/tenaga-kependidikan/{tenaga}', [TenagaKependidikanController::class, 'show'])->name('admin.tenaga-kependidikan.show');
    Route::get('/tenaga-kependidikan/{tenaga}/edit', [TenagaKependidikanController::class, 'edit'])->name('admin.tenaga-kependidikan.edit');
    Route::put('/tenaga-kependidikan/{tenaga}', [TenagaKependidikanController::class, 'update'])->name('admin.tenaga-kependidikan.update');
    Route::delete('/tenaga-kependidikan/{tenaga}', [TenagaKependidikanController::class, 'destroy'])->name('admin.tenaga-kependidikan.destroy');

    // Organisasi Routes
    Route::get('/organisasi', [OrganisasiController::class, 'index'])->name('admin.organisasi.index');
    Route::get('/organisasi/create', [OrganisasiController::class, 'create'])->name('admin.organisasi.create');
    Route::post('/organisasi', [OrganisasiController::class, 'store'])->name('admin.organisasi.store');
    Route::get('/organisasi/{organisasi}', [OrganisasiController::class, 'show'])->name('admin.organisasi.show');
    Route::get('/organisasi/{organisasi}/edit', [OrganisasiController::class, 'edit'])->name('admin.organisasi.edit');
    Route::put('/organisasi/{organisasi}', [OrganisasiController::class, 'update'])->name('admin.organisasi.update');
    Route::delete('/organisasi/{organisasi}', [OrganisasiController::class, 'destroy'])->name('admin.organisasi.destroy');

    // Prestasi Routes
    Route::get('/prestasi', [PrestasiController::class, 'index'])->name('admin.prestasi.index');
    Route::get('/prestasi/create', [PrestasiController::class, 'create'])->name('admin.prestasi.create');
    Route::post('/prestasi', [PrestasiController::class, 'store'])->name('admin.prestasi.store');
    Route::get('/prestasi/{prestasi}', [PrestasiController::class, 'show'])->name('admin.prestasi.show');
    Route::get('/prestasi/{prestasi}/edit', [PrestasiController::class, 'edit'])->name('admin.prestasi.edit');
    Route::put('/prestasi/{prestasi}', [PrestasiController::class, 'update'])->name('admin.prestasi.update');
    Route::delete('/prestasi/{prestasi}', [PrestasiController::class, 'destroy'])->name('admin.prestasi.destroy');

    // Alumni Routes
    Route::get('/alumni', [AlumniController::class, 'index'])->name('admin.alumni.index');
    Route::get('/alumni/create', [AlumniController::class, 'create'])->name('admin.alumni.create');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('admin.alumni.store');
    Route::get('/alumni/{alumni}', [AlumniController::class, 'show'])->name('admin.alumni.show');
    Route::get('/alumni/{alumni}/edit', [AlumniController::class, 'edit'])->name('admin.alumni.edit');
    Route::put('/alumni/{alumni}', [AlumniController::class, 'update'])->name('admin.alumni.update');
    Route::delete('/alumni/{alumni}', [AlumniController::class, 'destroy'])->name('admin.alumni.destroy');

    // Berita Routes
    Route::get('/berita', [BeritaController::class, 'index'])->name('admin.berita.index');
    Route::get('/berita/create', [BeritaController::class, 'create'])->name('admin.berita.create');
    Route::post('/berita', [BeritaController::class, 'store'])->name('admin.berita.store');
    Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('admin.berita.show');
    Route::get('/berita/{berita}/edit', [BeritaController::class, 'edit'])->name('admin.berita.edit');
    Route::put('/berita/{berita}', [BeritaController::class, 'update'])->name('admin.berita.update');
    Route::delete('/berita/{berita}', [BeritaController::class, 'destroy'])->name('admin.berita.destroy');

    // Artikel Routes
    Route::get('/artikel', [ArtikelController::class, 'index'])->name('admin.artikel.index');
    Route::get('/artikel/create', [ArtikelController::class, 'create'])->name('admin.artikel.create');
    Route::post('/artikel', [ArtikelController::class, 'store'])->name('admin.artikel.store');
    Route::get('/artikel/{artikel}', [ArtikelController::class, 'show'])->name('admin.artikel.show');
    Route::get('/artikel/{artikel}/edit', [ArtikelController::class, 'edit'])->name('admin.artikel.edit');
    Route::put('/artikel/{artikel}', [ArtikelController::class, 'update'])->name('admin.artikel.update');
    Route::delete('/artikel/{artikel}', [ArtikelController::class, 'destroy'])->name('admin.artikel.destroy');

    // Contact Messages Routes
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('admin.contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('admin.contact-messages.show');
    Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('admin.contact-messages.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
