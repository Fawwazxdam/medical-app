<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Patient;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * CsCreateAppointmentPage
 * 
 * Halaman custom untuk CS (Customer Service) membuat appointment baru.
 * 
 * Akses: Hanya untuk user dengan role 'cs' atau 'admin'
 */
class CsCreateAppointmentPage extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * Navigation label yang tampil di sidebar
     */
    protected static ?string $navigationLabel = 'Buat Appointment';

    /**
     * Judul halaman
     */
    protected static ?string $title = 'Buat Appointment Baru';

    /**
     * Icon untuk navigation
     */
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    /**
     * Urutan navigation di sidebar
     */
    protected static ?int $navigationSort = 2;

    /**
     * Navigation Group
     */
    protected static ?string $navigationGroup = 'Manajemen Appointment';

    /**
     * View file untuk halaman ini
     */
    protected static string $view = 'filament.pages.cs-create-appointment';

    /**
     * Data form
     */
    public ?array $data = [];

    /**
     * Otorisasi akses - untuk CS dan Admin
     */
    public static function canAccess(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['cs', 'admin']);
    }

    /**
     * Inisialisasi form saat halaman dimuat
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Konfigurasi form untuk membuat appointment
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Pilih Pasien dengan opsi membuat pasien baru
                Select::make('patient_id')
                    ->label('Pasien')
                    ->options(Patient::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih pasien...')
                    ->helperText('Cari dan pilih pasien yang akan membuat appointment')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Pasien')
                            ->required()
                            ->maxLength(255),

                        Select::make('gender')
                            ->required()
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ]),

                        DatePicker::make('date_of_birth')
                            ->label('Tanggal Lahir')
                            ->required()
                            ->displayFormat('d/m/Y'),

                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required(),

                        TextInput::make('address')
                            ->label('Alamat')
                            ->required()
                            ->maxLength(500),
                    ])
                    // Closure untuk menyimpan pasien baru dan mengembalikan ID
                    ->createOptionUsing(function (array $data): string {
                        $patient = Patient::create([
                            'name' => $data['name'],
                            'gender' => $data['gender'],
                            'date_of_birth' => $data['date_of_birth'],
                            'phone_number' => $data['phone_number'],
                            'address' => $data['address'],
                        ]);
                        return $patient->id;
                    }),

                // Pilih Dokter
                Select::make('doctor_id')
                    ->label('Dokter')
                    ->options(User::where('role', 'doctor')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->placeholder('Pilih dokter...')
                    ->helperText('Pilih dokter yang akan menangani pasien'),

                // Tanggal Appointment
                DatePicker::make('appointment_date')
                    ->label('Tanggal Appointment')
                    ->required()
                    ->minDate(now()->format('Y-m-d'))
                    ->displayFormat('d/m/Y')
                    ->helperText('Pilih tanggal appointment'),

                // Waktu Appointment
                TimePicker::make('appointment_time')
                    ->label('Waktu Appointment')
                    ->required()
                    ->seconds(false)
                    ->helperText('Pilih waktu appointment'),
            ])
            ->statePath('data')
            ->columns(2);
    }

    /**
     * Action untuk menyimpan appointment baru
     */
    public function create(): void
    {
        // Validasi form
        $data = $this->form->getState();

        // Gabungkan tanggal dan waktu menjadi timestamp
        $appointmentDateTime = $data['appointment_date'] . ' ' . $data['appointment_time'] . ':00';

        // Buat booking baru
        Booking::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'appointment_time' => $appointmentDateTime,
            'status' => 'Waiting',
        ]);

        // Reset form
        $this->form->fill();

        // Tampilkan notifikasi sukses
        Notification::make()
            ->title('Appointment Berhasil Dibuat')
            ->success()
            ->body('Appointment baru telah berhasil dibuat.')
            ->send();
    }
}