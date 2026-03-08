import { useParams } from "react-router-dom";
import { useState, useEffect } from "react";
import { FaUserCircle } from "react-icons/fa";
import "./UserProfileAdmin.css";

const UserProfileAdmin = () => {
  const { id } = useParams();
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const token = localStorage.getItem("token");
        const res = await fetch(`https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/korisnici/${id}`, {
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          },
        });

        if (!res.ok) {
          setUser(null);
          return;
        }

        const data = await res.json();
        setUser(data);
      } catch (err) {
        console.error(err);
        setUser(null);
      } finally {
        setLoading(false);
      }
    };

    fetchUser();
  }, [id]);

  if (loading) return <div className="loading">Učitavanje korisnika...</div>;
  if (!user) return <div className="not-found">Korisnik nije pronađen</div>;

  return (
    <div className="user-profile-page">
      {/* Profilna sekcija */}
      <div className="profile-section">
        <div className="profile-photo-container">
          {user.profile_photo_path ? (
            <img
              src={`https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/storage/${user.profile_photo_path}`}
              alt={`${user.ime} ${user.prezime}`}
              className="profile-photo"
            />
          ) : (
            <FaUserCircle className="profile-icon" />
          )}
        </div>
        <div className="profile-info">
          <h1>{user.ime} {user.prezime}</h1>
          <p><strong>Email:</strong> {user.email}</p>
          <p><strong>Tip korisnika:</strong> {user.tip_korisnika}</p>
          <p><strong>Datum rođenja:</strong> {new Date(user.datum_rodjenja).toLocaleDateString("sr-RS")}</p>
        </div>
      </div>

      {/* Zahtevi */}
<div className="section">
  <h2>Zahtevi</h2>
  {user.zahtevi?.length > 0 ? (
    <div className="cards-container">
      {user.zahtevi.map(z => (
        <div className="card" key={z.id}>
          <p><strong>Tip zahteva:</strong> {z.tip_zahteva === "prebivaliste" ? "Promena prebivališta" : "Promena bračnog statusa"}</p>
          <p><strong>Status:</strong> {z.status}</p>

          {/* Podaci za bracni_status */}
          {z.tip_zahteva === "bracni_status" && (
            <>
              <p><strong>Tip promene:</strong> {z.tip_promene}</p>
              <p><strong>Partner:</strong> {z.ime_partnera} {z.prezime_partnera}</p>
              <p><strong>Datum rođenja partnera:</strong> {z.datum_rodjenja_partnera ? new Date(z.datum_rodjenja_partnera).toLocaleDateString("sr-RS") : "N/A"}</p>
              <p><strong>Pol partnera:</strong> {z.partner_pol}</p>
              <p><strong>Broj ličnog dokumenta partnera:</strong> {z.broj_licnog_dokumenta_partnera}</p>
            </>
          )}

          {/* Podaci za prebivaliste */}
          {z.tip_zahteva === "prebivaliste" && (
            <>
              <p><strong>Stara adresa:</strong></p>
              {z.staraAdresa ? (
                <ul>
                  <li>Ulica: {z.staraAdresa.ulica} {z.staraAdresa.broj}</li>
                  <li>Mesto: {z.staraAdresa.mesto}</li>
                  <li>Opština: {z.staraAdresa.opstina}</li>
                  <li>Grad: {z.staraAdresa.grad}</li>
                  <li>Poštanski broj: {z.staraAdresa.postanski_broj}</li>
                  <li>Trajanje prebivališta: {z.staraAdresa.trajanje_prebivalista}</li>
                </ul>
              ) : <p>Nije dostupna</p>}
              
              <p><strong>Nova adresa:</strong></p>
              {z.novaAdresa ? (
                <ul>
                  <li>Ulica: {z.novaAdresa.ulica} {z.novaAdresa.broj}</li>
                  <li>Mesto: {z.novaAdresa.mesto}</li>
                  <li>Opština: {z.novaAdresa.opstina}</li>
                  <li>Grad: {z.novaAdresa.grad}</li>
                  <li>Poštanski broj: {z.novaAdresa.postanski_broj}</li>
                  <li>Trajanje prebivališta: {z.novaAdresa.trajanje_prebivalista}</li>
                </ul>
              ) : <p>Nije dostupna</p>}
            </>
          )}

          <p><strong>Datum kreiranja:</strong> {new Date(z.datum_kreiranja).toLocaleDateString("sr-RS")}</p>
        </div>
      ))}
    </div>
  ) : <p>Nema zahteva</p>}
</div>

      {/* Termini */}
      <div className="section">
        <h2>Termini</h2>
        {user.termini?.length > 0 ? (
          <div className="cards-container">
            {user.termini.map(t => (
              <div className="card" key={t.id}>
                <p><strong>Termin:</strong> {t.tip_dokumenta === "licna_karta" ? "Izdavanje lične karte" : "Izdavanje pasoša"}</p>
                <p><strong>Lokacija:</strong> {t.lokacija}</p>
                <p><strong>Datum i vreme:</strong> {new Date(t.datum_vreme).toLocaleString("sr-RS")}</p>
              </div>
            ))}
          </div>
        ) : <p>Nema termina</p>}
      </div>
    </div>
  );
};

export default UserProfileAdmin;