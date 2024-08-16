<?php

namespace App\Controllers;

use CodeIgniter\I18n\Time;
use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiSiswaModel;
use App\Models\PermissionRequestModel;
use App\Libraries\enums\TipeUser;

class Scan extends BaseController
{
    protected SiswaModel $siswaModel;
    protected GuruModel $guruModel;
    protected PresensiSiswaModel $presensiSiswaModel;
    protected PresensiGuruModel $presensiGuruModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->guruModel = new GuruModel();
        $this->presensiSiswaModel = new PresensiSiswaModel();
        $this->presensiGuruModel = new PresensiGuruModel();
    }

    public function index($t = 'Masuk')
    {
        $data = ['waktu' => $t, 'title' => 'Absensi Siswa dan Guru Berbasis QR Code'];
        return view('scan/scan', $data);
    }

    public function cekKode()
    {
        $uniqueCode = $this->request->getVar('unique_code');
        $waktuAbsen = $this->request->getVar('waktu');

        $status = false;
        $type = TipeUser::Siswa;

        // Cek data siswa di database
        $result = $this->siswaModel->cekSiswa($uniqueCode);

        if (empty($result)) {
            // Jika cek siswa gagal, cek data guru
            $result = $this->guruModel->cekGuru($uniqueCode);

            if (!empty($result)) {
                $status = true;
                $type = TipeUser::Guru;
            } else {
                return $this->showErrorView('Data tidak ditemukan');
            }
        } else {
            $status = true;
        }

        // Jika data ditemukan
        switch ($waktuAbsen) {
            case 'masuk':
                return $this->absenMasuk($type, $result);

            case 'pulang':
                return $this->absenPulang($type, $result);

            default:
                return $this->showErrorView('Data tidak valid');
        }
    }

    public function absenMasuk($type, $result)
    {
        $data['data'] = $result;
        $data['waktu'] = 'masuk';
        $date = Time::today()->toDateString();
        $time = Time::now()->toTimeString();

        // Time constraints
        $startCheckIn = '06:00:00'; // Check-in starts at 6 AM
        $endCheckIn = '08:00:00';   // Check-in ends at 11:59 PM

        // Check if the current time is within the allowed check-in time range
        if ($time < $startCheckIn || $time > $endCheckIn) {
            return $this->showErrorView('Check-in hanya bisa dilakukan antara pukul 06:00 hingga 07:00', $data);
        }

        switch ($type) {
            case TipeUser::Guru:
                $idGuru = $result['id_guru'];
                $data['type'] = TipeUser::Guru;

                $sudahAbsen = $this->presensiGuruModel->cekAbsen($idGuru, $date);
                if ($sudahAbsen) {
                    $data['presensi'] = $this->presensiGuruModel->getPresensiById($sudahAbsen);
                    return $this->showErrorView('Anda sudah absen hari ini', $data);
                }

                $this->presensiGuruModel->absenMasuk($idGuru, $date, $time);
                $data['presensi'] = $this->presensiGuruModel->getPresensiByIdGuruTanggal($idGuru, $date);

                return view('scan/scan-result', $data);

            case TipeUser::Siswa:
                $idSiswa = $result['id_siswa'];
                $idKelas = $result['id_kelas'];
                $data['type'] = TipeUser::Siswa;

                $sudahAbsen = $this->presensiSiswaModel->cekAbsen($idSiswa, $date);
                if ($sudahAbsen) {
                    $data['presensi'] = $this->presensiSiswaModel->getPresensiById($sudahAbsen);
                    return $this->showErrorView('Anda sudah absen hari ini', $data);
                }

                $this->presensiSiswaModel->absenMasuk($idSiswa, $date, $time, $idKelas);
                $data['presensi'] = $this->presensiSiswaModel->getPresensiByIdSiswaTanggal($idSiswa, $date);

                return view('scan/scan-result', $data);

            default:
                return $this->showErrorView('Tipe tidak valid');
        }
    }

    public function absenPulang($type, $result)
    {
        $data['data'] = $result;
        $data['waktu'] = 'pulang';
        $date = Time::today()->toDateString();
        $time = Time::now()->toTimeString();

        // Time constraints
        $startCheckOut = '15:00:00'; // Check-out starts at midnight
        $endCheckOut = '17:00:00';   // Check-out ends at 9 PM

        // Check if the current time is within the allowed check-out time range
        if ($time < $startCheckOut || $time > $endCheckOut) {
            return $this->showErrorView('Check-out hanya bisa dilakukan antara pukul 15:00 hingga 17:00', $data);
        }

        switch ($type) {
            case TipeUser::Guru:
                $idGuru = $result['id_guru'];
                $data['type'] = TipeUser::Guru;

                $sudahAbsen = $this->presensiGuruModel->cekAbsen($idGuru, $date);
                if (!$sudahAbsen) {
                    return $this->showErrorView('Anda belum absen hari ini', $data);
                }

                $this->presensiGuruModel->absenKeluar($sudahAbsen, $time);
                $data['presensi'] = $this->presensiGuruModel->getPresensiById($sudahAbsen);

                return view('scan/scan-result', $data);

            case TipeUser::Siswa:
                $idSiswa = $result['id_siswa'];
                $data['type'] = TipeUser::Siswa;

                $sudahAbsen = $this->presensiSiswaModel->cekAbsen($idSiswa, $date);
                if (!$sudahAbsen) {
                    return $this->showErrorView('Anda belum absen hari ini', $data);
                }

                $this->presensiSiswaModel->absenKeluar($sudahAbsen, $time);
                $data['presensi'] = $this->presensiSiswaModel->getPresensiById($sudahAbsen);

                return view('scan/scan-result', $data);

            default:
                return $this->showErrorView('Tipe tidak valid');
        }
    }

    public function requestPermission()
    {
        $userId = $this->request->getVar('user_id');
        $userType = $this->request->getVar('user_type'); // 'siswa' atau 'guru'
        $reason = $this->request->getVar('reason');
        $date = $this->request->getVar('date');

        if (!$userId || !$userType || !$reason || !$date) {
            return $this->showErrorView('Data tidak lengkap');
        }

        $data = [
            'user_id' => $userId,
            'user_type' => $userType,
            'reason' => $reason,
            'date' => $date,
        ];

        return view('scan/permission-request-success', $data);
    }

    public function showErrorView(string $msg = 'no error message', array $data = [])
    {
        $data['msg'] = $msg;
        return view('scan/error-scan-result', $data);
    }
}
