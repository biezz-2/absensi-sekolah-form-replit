"use client";

import { useState, useEffect } from "react";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
// import { PeriodPicker } from "@/components/period-picker";

interface AttendanceRecord {
  id: number;
  student: {
    user: {
      name: string;
    };
    student_id_number: string;
  };
  session: {
    classroom: {
      name: string;
    };
    name: string;
  };
  check_in_time: string;
  is_valid: boolean;
  reason: string | null;
}

interface AttendanceHistory {
  data: AttendanceRecord[];
  current_page: number;
  last_page: number;
  total: number;
}

export default function AttendanceHistory() {
  const [attendanceData, setAttendanceData] = useState<AttendanceHistory | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [dateFilter, setDateFilter] = useState({
    date_from: '',
    date_to: ''
  });

  const fetchAttendanceHistory = async (page = 1) => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        page: page.toString(),
        ...(dateFilter.date_from && { date_from: dateFilter.date_from }),
        ...(dateFilter.date_to && { date_to: dateFilter.date_to })
      });

      const response = await fetch(`/api/attendance/history?${params}`);
      
      if (!response.ok) {
        throw new Error('Failed to fetch attendance history');
      }
      
      const data = await response.json();
      setAttendanceData(data);
      setError(null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'An error occurred');
      console.error('Error fetching attendance history:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAttendanceHistory(currentPage);
  }, [currentPage, dateFilter]);

  // Handle date filter changes

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date);
  };

  const getStatusBadge = (isValid: boolean, reason: string | null) => {
    if (isValid) {
      return (
        <span className="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-800/20 dark:text-green-400">
          ✓ Valid
        </span>
      );
    } else {
      return (
        <span className="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-800/20 dark:text-red-400">
          ✗ {reason === 'outside_radius' ? 'Di luar radius' : 'Tidak valid'}
        </span>
      );
    }
  };

  const getTotalStats = () => {
    if (!attendanceData?.data) return { total: 0, valid: 0, invalid: 0 };
    
    const total = attendanceData.data.length;
    const valid = attendanceData.data.filter(record => record.is_valid).length;
    const invalid = total - valid;
    
    return { total, valid, invalid };
  };

  const stats = getTotalStats();

  return (
    <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
      <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 className="text-title-md2 font-semibold text-black dark:text-white">
          Riwayat Absensi
        </h2>
        <div className="flex gap-2">
          <input
            type="date"
            value={dateFilter.date_from}
            onChange={(e) => setDateFilter(prev => ({ ...prev, date_from: e.target.value }))}
            className="rounded border border-stroke px-3 py-2 text-sm dark:border-stroke-dark dark:bg-gray-dark dark:text-white"
            placeholder="Dari tanggal"
          />
          <input
            type="date"
            value={dateFilter.date_to}
            onChange={(e) => setDateFilter(prev => ({ ...prev, date_to: e.target.value }))}
            className="rounded border border-stroke px-3 py-2 text-sm dark:border-stroke-dark dark:bg-gray-dark dark:text-white"
            placeholder="Sampai tanggal"
          />
          <button
            onClick={() => setDateFilter({ date_from: '', date_to: '' })}
            className="rounded bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
          >
            Reset
          </button>
        </div>
      </div>

      {/* Statistics Cards */}
      <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div className="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-stroke-dark dark:bg-gray-dark">
          <div className="flex items-center">
            <div className="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-800/20">
              <span className="text-lg font-semibold text-blue-600 dark:text-blue-400">
                {stats.total}
              </span>
            </div>
            <div className="ml-4">
              <p className="text-sm text-body-color">Total Absensi</p>
              <h4 className="text-xl font-bold text-black dark:text-white">
                {attendanceData?.total || 0}
              </h4>
            </div>
          </div>
        </div>

        <div className="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-stroke-dark dark:bg-gray-dark">
          <div className="flex items-center">
            <div className="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-green-100 dark:bg-green-800/20">
              <span className="text-lg font-semibold text-green-600 dark:text-green-400">
                {stats.valid}
              </span>
            </div>
            <div className="ml-4">
              <p className="text-sm text-body-color">Absensi Valid</p>
              <h4 className="text-xl font-bold text-black dark:text-white">
                {stats.valid}
              </h4>
            </div>
          </div>
        </div>

        <div className="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-stroke-dark dark:bg-gray-dark">
          <div className="flex items-center">
            <div className="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-red-100 dark:bg-red-800/20">
              <span className="text-lg font-semibold text-red-600 dark:text-red-400">
                {stats.invalid}
              </span>
            </div>
            <div className="ml-4">
              <p className="text-sm text-body-color">Absensi Tidak Valid</p>
              <h4 className="text-xl font-bold text-black dark:text-white">
                {stats.invalid}
              </h4>
            </div>
          </div>
        </div>
      </div>

      <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-stroke-dark dark:bg-gray-dark">
        <div className="px-4 py-6 md:px-6 xl:px-7.5">
          <h4 className="text-xl font-semibold text-black dark:text-white">
            Data Riwayat Kehadiran
          </h4>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
            <span className="ml-3 text-body-color">Memuat data...</span>
          </div>
        ) : error ? (
          <div className="flex items-center justify-center py-12">
            <div className="text-center">
              <p className="text-red-600 dark:text-red-400">Error: {error}</p>
              <button 
                onClick={() => fetchAttendanceHistory(currentPage)}
                className="mt-2 rounded bg-primary px-4 py-2 text-white hover:bg-primary/90"
              >
                Coba Lagi
              </button>
            </div>
          </div>
        ) : attendanceData?.data.length === 0 ? (
          <div className="flex items-center justify-center py-12">
            <p className="text-body-color">Tidak ada data riwayat absensi</p>
          </div>
        ) : (
          <>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Nama Siswa</TableHead>
                  <TableHead>NIM</TableHead>
                  <TableHead>Kelas</TableHead>
                  <TableHead>Sesi</TableHead>
                  <TableHead>Waktu Absensi</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {attendanceData?.data.map((record) => (
                  <TableRow key={record.id}>
                    <TableCell className="font-medium">
                      {record.student.user.name}
                    </TableCell>
                    <TableCell>
                      {record.student.student_id_number}
                    </TableCell>
                    <TableCell>
                      {record.session.classroom.name}
                    </TableCell>
                    <TableCell>
                      {record.session.name || 'Sesi Absensi'}
                    </TableCell>
                    <TableCell>
                      {formatDate(record.check_in_time)}
                    </TableCell>
                    <TableCell>
                      {getStatusBadge(record.is_valid, record.reason)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>

            {/* Pagination */}
            {attendanceData && attendanceData.last_page > 1 && (
              <div className="flex items-center justify-between border-t border-stroke px-4 py-4 dark:border-stroke-dark">
                <p className="text-sm text-body-color">
                  Halaman {attendanceData.current_page} dari {attendanceData.last_page}
                  {attendanceData.total && ` (${attendanceData.total} total)`}
                </p>
                <div className="flex gap-2">
                  <button
                    onClick={() => setCurrentPage(currentPage - 1)}
                    disabled={currentPage <= 1}
                    className="rounded bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                  >
                    Sebelumnya
                  </button>
                  <button
                    onClick={() => setCurrentPage(currentPage + 1)}
                    disabled={currentPage >= attendanceData.last_page}
                    className="rounded bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                  >
                    Selanjutnya
                  </button>
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
