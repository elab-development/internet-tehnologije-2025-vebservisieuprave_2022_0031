import { useParams, useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { FaUserCircle } from "react-icons/fa";
import "./RequestDetailsAdmin.css";

const RequestDetailsAdmin = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [zahtev, setZahtev] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchZahtev = async () => {
      try {
        const token = localStorage.getItem("token");
        const res = await fetch(`https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/zahtevi/${id}`, {
          headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
          },
        });
        const data = await res.json();
        setZahtev(data);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetchZahtev();
  }, [id]);

  const updateStatus = async (status) => {
    try {
      const token = localStorage.getItem("token");
      const res = await fetch(`https://internet-tehnologije-2025-vebservisieuprave2022-production.up.railway.app/api/admin/zahtevi/${id}/${status}`, {
        method: "POST",
       headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + token
        },
        body: JSON.stringify({})
      });
      const data = await res.json();
      alert(data.message);
      
    navigate("/admin/neobradjeniZahtevi"); // vraća na prethodnu stranicu
    } catch (err) {
      console.error(err);
    }
  };

  if (loading) return <div>Učitavanje detalja zahteva...</div>;
  if (!zahtev) return <div>Zahtev nije pronađen</div>;

  const user = zahtev.korisnik;
  const stara = zahtev.stara_adresa;
  const nova = zahtev.nova_adresa;

  return (
    <div className="request-details-page">
      <div className="request-details-grid">
        {/* Leva strana - korisnik */}
        <div className="user-info">
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
          <h2>{user.ime} {user.prezime}</h2>
          <p><strong>Email:</strong> {user.email}</p>
          <p><strong>Tip korisnika:</strong> {user.tip_korisnika}</p>
          <p><strong>Datum rođenja:</strong> {new Date(user.datum_rodjenja).toLocaleDateString()}</p>
        </div>

        {/* Desna strana - zahtev */}
        <div className="request-info">
          <h3>{zahtev.tip_zahteva === 'prebivaliste' ? 'Promena prebivališta' : 'Promena bračnog statusa'}</h3>
          <p><strong>Status:</strong> {zahtev.status}</p>
          <p><strong>Datum kreiranja:</strong> {new Date(zahtev.datum_kreiranja).toLocaleString()}</p>
          {zahtev.tip_zahteva === 'bracni_status' && (
            <>
              <p><strong>Tip promene:</strong>{" "}
                {zahtev.tip_promene === 'sklapanje_braka'
                ? 'Sklapanje braka'
                : zahtev.tip_promene === 'razvod'
                ? 'Razvod'
                : zahtev.tip_promene}</p>
              <p><strong>Partner:</strong> {zahtev.ime_partnera} {zahtev.prezime_partnera}</p>
              <p><strong>Datum rođenja partnera:</strong> {new Date(zahtev.datum_rodjenja_partnera).toLocaleDateString()}</p>
              <p><strong>Pol partnera:</strong> {zahtev.partner_pol}</p>
            </>
          )}
          {zahtev.tip_zahteva === 'prebivaliste' && (
            <>
              <h4>Stara adresa</h4>
              {stara ? (
                <p>{stara.ulica} {stara.broj}, {stara.mesto}, {stara.grad}, {stara.postanski_broj} ({stara.trajanje_prebivalista})</p>
              ) : <p>Nema podatka</p>}
              <h4>Nova adresa</h4>
              {nova ? (
                <p>{nova.ulica} {nova.broj}, {nova.mesto}, {nova.grad}, {nova.postanski_broj} ({nova.trajanje_prebivalista})</p>
              ) : <p>Nema podatka</p>}
            </>
          )}
          <div className="request-buttons">
            <button onClick={() => updateStatus('odobri')}>Odobri</button>
            <button onClick={() => updateStatus('odbij')}>Odbi</button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RequestDetailsAdmin;