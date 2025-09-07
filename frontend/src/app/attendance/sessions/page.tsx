"use client";

export default function AttendanceSessions() {
  return (
    <div className="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
      <div className="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 className="text-title-md2 font-semibold text-black dark:text-white">
          Sesi Absensi
        </h2>
      </div>

      <div className="rounded-sm border border-stroke bg-white shadow-default dark:border-stroke-dark dark:bg-gray-dark">
        <div className="px-4 py-6 md:px-6 xl:px-7.5">
          <h4 className="text-xl font-semibold text-black dark:text-white">
            Kelola Sesi Absensi
          </h4>
        </div>

        <div className="grid grid-cols-1 border-t border-stroke px-4 py-4.5 dark:border-stroke-dark sm:grid-cols-2 md:px-6 2xl:px-7.5">
          <div className="flex items-center">
            <p className="font-medium text-black dark:text-white">
              Fitur ini akan segera tersedia
            </p>
          </div>
          <div className="flex items-center justify-end">
            <p className="text-sm text-body-color dark:text-body-color">
              Buat dan kelola sesi absensi kelas
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
