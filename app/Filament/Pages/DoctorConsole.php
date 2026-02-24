<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\MedicalRecord;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DoctorConsole Page
 * 
 * Halaman custom Filament untuk dokter melihat daftar pasien hari ini
 * dan memulai pemeriksaan. Menggunakan InteractsWithTable trait untuk
 * menampilkan tabel booking dengan filter berdasarkan tanggal hari ini.
 * 
 * Akses: Hanya untuk user dengan role 'doctor'
 */
class DoctorConsole extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * Navigation label yang tampil di sidebar
     */
    protected static ?string $navigationLabel = 'Doctor Console';

    /**
     * Judul halaman
     */
    protected static ?string $title = 'Doctor Console';

    /**
     * Icon untuk navigation
     */
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    /**
     * Urutan navigation di sidebar
     */
    protected static ?int $navigationSort = 1;

    /**
     * View file untuk halaman ini
     */
    protected static string $view = 'filament.pages.doctor-console';

    /**
     * Otorisasi akses - hanya untuk dokter
     * Halaman ini hanya bisa diakses oleh user dengan role 'doctor'
     */
    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'doctor';
    }

    /**
     * Konfigurasi tabel untuk menampilkan booking hari ini
     * 
     * Filter default: appointment_time = hari ini DAN doctor_id = auth()->id()
     * Menampilkan semua status: Waiting, InProgress, Finished, Cancelled
     * Kolom: Nama Pasien, Jenis Kelamin, Tanggal Lahir, Waktu Appointment, Status
     */
    public function table(Table $table): Table
    {
        return $table
            // Query dasar dengan filter untuk dokter yang sedang login dan hari ini
            ->query(
                Booking::query()
                    ->where('doctor_id', Auth::id())
                    ->whereDate('appointment_time', today())
                    ->with('patient', 'medicalRecord') // Eager loading untuk optimasi performa
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

                // Kolom Tanggal Lahir
                TextColumn::make('patient.date_of_birth')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),

                // Kolom Waktu Appointment
                TextColumn::make('appointment_time')
                    ->label('Waktu Appointment')
                    ->dateTime('H:i')
                    ->sortable()
                    ->description(fn(Booking $record): string => $record->appointment_time->format('d M Y')),

                // Kolom Status dengan warna berbeda
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

                // Kolom Diagnosis (hanya untuk yang sudah selesai)
                TextColumn::make('medicalRecord.diagnosis')
                    ->label('Diagnosis')
                    ->limit(30)
                    ->tooltip(fn(Booking $record): ?string => $record->medicalRecord?->diagnosis)
                    ->placeholder('-'),
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
                    ])
                    ->default('Waiting'),
            ])
            ->actions([
                // Action Button "Mulai Periksa" - hanya untuk status Waiting
                Action::make('mulaiPeriksa')
                    ->label('Mulai Periksa')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn(Booking $record): bool => $record->status === 'Waiting')
                    ->modalHeading('Mulai Pemeriksaan')
                    ->modalDescription('Isi diagnosis dan catatan untuk pemeriksaan ini.')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    // Form modal dengan field diagnosis dan notes
                    ->form([
                        Textarea::make('diagnosis')
                            ->label('Diagnosis')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan diagnosis pasien...'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->rows(3)
                            ->placeholder('Catatan tambahan (opsional)...'),
                    ])
                    // Action saat form disubmit
                    ->action(function (array $data, Booking $record): void {
                        // Gunakan database transaction untuk memastikan konsistensi data
                        DB::transaction(function () use ($data, $record): void {
                            // 1. Update booking: set status, started_at, finished_at
                            $record->update([
                                'status' => 'Finished',
                                'started_at' => now(),
                                'finished_at' => now(),
                            ]);

                            // 2. Insert ke medical_records
                            MedicalRecord::create([
                                'appointment_id' => $record->id,
                                'patient_id' => $record->patient_id,
                                'doctor_id' => Auth::id(),
                                'diagnosis' => $data['diagnosis'],
                                'notes' => $data['notes'] ?? null,
                            ]);
                        });

                        // Tampilkan notifikasi sukses
                        Notification::make()
                            ->title('Pemeriksaan Selesai')
                            ->success()
                            ->body('Data pemeriksaan berhasil disimpan.')
                            ->send();
                    }),

                // Action Button "Lihat Detail" - untuk yang sudah selesai
                Action::make('lihatDetail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn(Booking $record): bool => $record->status === 'Finished')
                    ->modalHeading('Detail Pemeriksaan')
                    ->modalDescription(fn(Booking $record): string => "Pasien: {$record->patient->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->form([
                        Textarea::make('diagnosis')
                            ->label('Diagnosis')
                            ->disabled()
                            ->rows(3)
                            ->default(fn(Booking $record): string => $record->medicalRecord?->diagnosis ?? '-'),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->disabled()
                            ->rows(3)
                            ->default(fn(Booking $record): string => $record->medicalRecord?->notes ?? '-'),
                    ]),
            ])
            ->defaultSort('appointment_time', 'asc')
            ->emptyStateHeading('Tidak Ada Pasien Hari Ini')
            ->emptyStateDescription('Belum ada pasien yang terjadwal untuk hari ini.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
