import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import UserCard from "../components/UserCard";
import "./UsersPanelAdmin.css";

const UsersPanelAdmin = ({ user }) => {
  const navigate = useNavigate();

  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [filter, setFilter] = useState("svi");

  const fetchUsers = async (tip = "svi") => {
    setLoading(true);
    setError("");

    try {
      const token = localStorage.getItem("token");

      let url =
        "https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/korisnici";

      if (tip !== "svi") {
        url += `?tip_korisnika=${tip}`;
      }

      const res = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
          Authorization: "Bearer " + token,
        },
      });

      if (!res.ok) {
        throw new Error("Greška pri učitavanju korisnika");
      }

      const data = await res.json();
      setUsers(data.users);
    } catch (err) {
      setError(err.message || "Došlo je do greške");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!user || user.tip_korisnika !== "admin") {
      navigate("/login");
      return;
    }

    fetchUsers(filter);
  }, [user, navigate, filter]); // ← filter dodat

  const handleFilterChange = (e) => {
    setFilter(e.target.value); // ← samo menja filter, useEffect se brine za ostalo
  };

  if (loading) return <div className="users-loading">Učitavanje korisnika...</div>;
  if (error) return <div className="users-error">{error}</div>;

  return (
    <div className="admin-users-page">
      <h1>Lista korisnika</h1>

      <div className="filter-container">
        <label htmlFor="filter">Filter po tipu:</label>
        <select id="filter" value={filter} onChange={handleFilterChange}>
          <option value="svi">Svi</option>
          <option value="strani">Strani državljanin</option>
          <option value="domaci">Domaći državljanin</option>
        </select>
      </div>

      <div className="users-grid">
        {users.map((u) => (
          <UserCard key={u.id} user={u} />
        ))}
      </div>
    </div>
  );
};

export default UsersPanelAdmin;