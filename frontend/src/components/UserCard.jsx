import React from "react";
import { useNavigate } from "react-router-dom";
import "./UserCard.css";

const UserCard = ({ user }) => {
  const navigate = useNavigate();

  const handleClick = () => {
    navigate(`/admin/korisnici/${user.id}`);
  };

  return (
    <div className="user-card" onClick={handleClick} style={{ cursor: "pointer" }}>
      <h3>{user.ime} {user.prezime}</h3>
      <p>Email: {user.email}</p>
      <p>Tip: {user.tip_korisnika}</p>
      <p>
        Datum rođenja: {new Date(user.datum_rodjenja).toLocaleDateString("sr-RS")}
      </p>
    </div>
  );
};

export default UserCard;