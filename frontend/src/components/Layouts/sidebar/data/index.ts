import * as Icons from "../icons";

export const NAV_DATA = [
  {
    label: "MENU UTAMA",
    items: [
      {
        title: "Dashboard",
        icon: Icons.HomeIcon,
        items: [
          {
            title: "Beranda",
            url: "/",
          },
        ],
      },
      {
        title: "Absensi",
        icon: Icons.Calendar,
        items: [
          {
            title: "Pemindai QR",
            url: "/scan",
          },
          {
            title: "Riwayat Absensi",
            url: "/attendance/history",
          },
        ],
      },
      {
        title: "Kelas",
        icon: Icons.Table,
        items: [
          {
            title: "Daftar Kelas",
            url: "/classes",
          },
          {
            title: "Sesi Absensi",
            url: "/attendance/sessions",
          },
        ],
      },
      {
        title: "Siswa",
        icon: Icons.User,
        items: [
          {
            title: "Daftar Siswa",
            url: "/students",
          },
          {
            title: "Profil Siswa",
            url: "/profile",
          },
        ],
      },
    ],
  },
  {
    label: "PENGATURAN",
    items: [
      {
        title: "Laporan",
        icon: Icons.PieChart,
        items: [
          {
            title: "Laporan Absensi",
            url: "/reports/attendance",
          },
          {
            title: "Statistik",
            url: "/reports/statistics",
          },
        ],
      },
      {
        title: "Pengaturan",
        icon: Icons.FourCircle,
        items: [
          {
            title: "Pengaturan Sistem",
            url: "/settings",
          },
          {
            title: "Profil Pengguna",
            url: "/profile",
          },
        ],
      },
    ],
  },
];

