<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * CsBookingListPage
 * 
 * Halaman untuk CS melihat daftar semua booking/appointment.
 * Read-only - tidak ada aksi edit atau delete.
 * 
 * Akses: Hanya untuk user dengan role 'cs' atau 'admin'
 */
class CsBookingListPage extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * Navigation label yang tampil di sidebar
     */
    protected static ?string $navigationLabel = 'Daftar Appointment';

    /**
     * Judul halaman
     */
    protected static ?string $title = 'Daftar Appointment';

    /**
     * Icon untuk navigation
     */
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    /**
     * Urutan navigation di sidebar
     */
    protected static ?int $navigationSort = 3;

    /**
     * Navigation Group
     */
    protected static ?string $navigationGroup = 'Manajemen Appointment';

    /**
     * View file untuk halaman ini
     */
    protected static string $view = 'filament.pages.cs-booking-list';

    /**
     * Otorisasi akses - untuk CS dan Admin
     */
    public static function canAccess(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['cs', 'admin']);
    }

    /**
     * Konfigurasi tabel untuk menampilkan daftar booking (read-only)
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['patient', 'doctor'])
                    ->latest('appointment_time')
            )
            ->columns([
                // Kolom Nama Pasien
                TextColumn::make('patient.name')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->sortable(),

                // Kolom Jenis Kelamin
                TextColumn::make('patient.gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn(string $state): string => $state === 'male' ? 'Laki-laki' : 'Perempuan')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'male' ? 'info' : 'pink'),

                // Kolom Dokter
                TextColumn::make('doctor.name')
                    ->label('Dokter')
                    ->searchable()
                    ->sortable(),

                // Kolom Waktu Appointment
                TextColumn::make('appointment_time')
                    ->label('Waktu Appointment')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                // Kolom Status
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Waiting' => 'Menunggu',
                        'InProgress' => 'Sedang Diperiksa',
                        'Finished' => 'Selesai',
                        'Cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Waiting' => 'warning',
                        'InProgress' => 'info',
                        'Finished' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // Filter berdasarkan status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Waiting' => 'Menunggu',
                        'InProgress' => 'Sedang Diperiksa',
                        'Finished' => 'Selesai',
                        'Cancelled' => 'Dibatalkan',
                    ]),

                // Filter berdasarkan dokter
                SelectFilter::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('appointment_time', 'desc')
            ->emptyStateHeading('Belum Ada Appointment')
            ->emptyStateDescription('Belum ada appointment yang dibuat.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}