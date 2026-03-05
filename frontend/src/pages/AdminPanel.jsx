import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Chart } from "react-google-charts";

import "./AdminPanel.css";

const AdminPanel = ({ user }) => {
  const navigate = useNavigate();
  const [stats, setStats] = useState(null);
  const [reqStats, setReqStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!user || user.tip_korisnika !== "admin") {
      navigate("/login");
      return;
    }

    const token = localStorage.getItem("token");
    if (!token) {
      setError("Niste ulogovani. Molimo prijavite se.");
      setLoading(false);
      return;
    }

    const fetchStats = async () => {
      setLoading(true);
      setError("");

      try {
        const resUsers = await fetch("http://localhost:8000/api/admin/statistika", {
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          },
        });
        if (!resUsers.ok) throw new Error("Greška pri učitavanju statistike korisnika");
        const dataUsers = await resUsers.json();
        setStats(dataUsers);

        const resReq = await fetch("http://localhost:8000/api/admin/statistikaZahteva", {
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          },
        });
        if (!resReq.ok) throw new Error("Greška pri učitavanju statistike zahteva");
        const dataReq = await resReq.json();
        setReqStats(dataReq);
      } catch (err) {
        setError(err.message || "Došlo je do greške");
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, [user, navigate]);

  if (loading) return <div className="admin-loading">Učitavanje...</div>;
  if (error) return <div className="admin-error">{error}</div>;

  const chartDataUsers = [
    ["Tip korisnika", "Broj"],
    ["Domaći", stats.totalDomaci],
    ["Strani", stats.totalStrani],
  ];

  const chartOptionsUsers = {
    title: "Distribucija korisnika",
    pieHole: 0.4,
    colors: ["#4CAF50", "#2196F3"],
    legend: { position: "bottom" },
  };

  const chartDataReq = [
    ["Status zahteva", "Broj"],
    ["Čekajući", reqStats.cekajuci],
    ["Odobreni", reqStats.odobreni],
    ["Odbijeni", reqStats.odbijeni],
  ];

  const chartOptionsReq = {
    title: "Statistika zahteva",
    pieHole: 0.4,
    colors: ["#FFC107", "#4CAF50", "#F44336"],
    legend: { position: "bottom" },
  };

  return (
    <div className="admin-page">
      <h1 className="admin-title">Admin Panel</h1>

      {/* Prvi red: kartice */}
      <div className="admin-cards-row">
        <div className="admin-card">
          <h3>Ukupno korisnika</h3>
          <p>{stats.totalUsers}</p>
        </div>
        <div className="admin-card">
          <h3>Domaći korisnici</h3>
          <p>{stats.totalDomaci}</p>
        </div>
        <div className="admin-card">
          <h3>Strani korisnici</h3>
          <p>{stats.totalStrani}</p>
        </div>
      </div>

      {/* Drugi red: kartice za zahteve */}
      <div className="admin-cards-row">
        <div className="admin-card">
          <h3>Čekajući zahtevi</h3>
          <p>{reqStats.cekajuci}</p>
        </div>
        <div className="admin-card">
          <h3>Odobreni zahtevi</h3>
          <p>{reqStats.odobreni}</p>
        </div>
        <div className="admin-card">
          <h3>Odbijeni zahtevi</h3>
          <p>{reqStats.odbijeni}</p>
        </div>
      </div>

      {/* Treći red: grafici */}
      <div className="admin-charts-row">
        <div className="admin-chart">
          <Chart
            chartType="PieChart"
            width="100%"
            height="300px"
            data={chartDataUsers}
            options={chartOptionsUsers}
          />
        </div>
        <div className="admin-chart">
          <Chart
            chartType="PieChart"
            width="100%"
            height="300px"
            data={chartDataReq}
            options={chartOptionsReq}
          />
        </div>
      </div>
    </div>
  );
};

export default AdminPanel;