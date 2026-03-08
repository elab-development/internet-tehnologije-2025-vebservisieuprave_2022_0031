import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/api";
import UserCard from "../components/UserCard";
import "./UsersPanelAdmin.css";

const UsersPanelAdmin = ({ user }) => {
  const navigate = useNavigate();
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    // Provera da li je ulogovan admin
    if (!user || user.tip_korisnika !== "admin") {
      navigate("/login");
      return;
    }

    const fetchUsers = async () => {
      setLoading(true);
      setError("");
      try {
        const token = localStorage.getItem("token");
        const res = await fetch("https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/korisnici", {
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

    fetchUsers();
  }, [user, navigate]);

  if (loading) return <div className="users-loading">Učitavanje korisnika...</div>;
  if (error) return <div className="users-error">{error}</div>;

  return (
    <div className="admin-users-page">
      <h1>Lista korisnika</h1>
      <div className="users-grid">
        {users.map((u) => (
        <UserCard key={u.id} user={u} />
    ))}
      </div>
    </div>
  );
};

export default UsersPanelAdmin;