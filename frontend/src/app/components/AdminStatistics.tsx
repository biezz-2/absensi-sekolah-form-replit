import { useEffect, useState } from "react";

export default function AdminStatistics() {
  const [stats, setStats] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetch("/api/dashboard/statistics")
      .then(async (res) => {
        if (!res.ok) throw new Error("Failed to fetch statistics");
        setStats(await res.json());
      })
      .catch(() => setError("Gagal memuat statistik"))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Memuat statistik...</div>;
  if (error) return <div style={{color: 'red'}}>{error}</div>;
  if (!stats) return null;

  return (
    <div style={{
      display: 'flex',
      gap: 24,
      margin: '32px 0',
      flexWrap: 'wrap',
      justifyContent: 'center',
    }}>
      <StatBox label="Total Siswa" value={stats.total_students} color="#4f8cff" />
      <StatBox label="Total Guru" value={stats.total_teachers} color="#ffb14f" />
      <StatBox label="Total Kelas" value={stats.total_classes} color="#4fff8c" />
      <StatBox label="Total Absensi" value={stats.total_attendance_records} color="#ff4f8c" />
    </div>
  );
}

function StatBox({ label, value, color }: { label: string, value: number, color: string }) {
  return (
    <div style={{
      minWidth: 160,
      background: color,
      color: '#fff',
      borderRadius: 12,
      padding: '24px 20px',
      textAlign: 'center',
      boxShadow: '0 2px 8px #0001',
      fontWeight: 600,
      fontSize: 18,
    }}>
      <div style={{ fontSize: 36, fontWeight: 700, marginBottom: 8 }}>{value}</div>
      <div>{label}</div>
    </div>
  );
}
