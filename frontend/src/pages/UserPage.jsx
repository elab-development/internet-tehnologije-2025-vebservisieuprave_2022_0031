import { useEffect, useState } from "react";
import api from "../api/api";
import "./UserPage.css";

export default function UserPage() {
  const [user, setUser] = useState(null);
  const [email, setEmail] = useState("");
  const [photo, setPhoto] = useState(null);
  const [preview, setPreview] = useState(null);

  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const [message, setMessage] = useState("");
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);

  const formatDate = (dateString) => {
    if (!dateString) return "";

    const date = new Date(dateString);

    const formatted = date.toLocaleDateString("sr-RS", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });

    const cirToLat = {
      а: "a", б: "b", в: "v", г: "g", д: "d", ђ: "đ", е: "e", ж: "ž",
      з: "z", и: "i", ј: "j", к: "k", л: "l", љ: "lj", м: "m", н: "n",
      њ: "nj", о: "o", п: "p", р: "r", с: "s", т: "t", ћ: "ć", у: "u",
      ф: "f", х: "h", ц: "c", ч: "č", џ: "dž", ш: "š"
    };

    return formatted.replace(/[а-яђжљњћџ]/g, (c) => cirToLat[c] || c);
  };

  const formatPol = (pol) => {
    if (pol === "Z") return "Ženski";
    if (pol === "M") return "Muški";
    return pol;
  };

  const formatTipKorisnika = (tip) => {
    if (tip === "domaci") return "Domaći državljanin";
    if (tip === "strani") return "Strani državljanin";
    if (tip === "admin") return "Administrator";
    return tip;
  };

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await api.get("/me");
        setUser(res.data);
        setEmail(res.data.email);
      } catch (err) {
        console.error(err);
        setMessage("Greška pri učitavanju korisnika.");
      } finally {
        setLoading(false);
      }
    };

    fetchUser();
  }, []);

  const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    setPhoto(file);
    if (file) setPreview(URL.createObjectURL(file));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage("");
    setErrors({});

    const formData = new FormData();
    formData.append("email", email);

    if (photo) {
      formData.append("profile_photo", photo);
    }

    if (newPassword) {
      formData.append("current_password", currentPassword);
      formData.append("new_password", newPassword);
      formData.append("new_password_confirmation", confirmPassword);
    }

    try {
      const res = await api.post("/profile?_method=PUT", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      setMessage(res.data.message);
      setUser(res.data.user);

      setCurrentPassword("");
      setNewPassword("");
      setConfirmPassword("");
      setPhoto(null);
    } catch (err) {
      console.error(err);
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setMessage(err.response?.data?.message || "Greška.");
      }
    }
  };

  if (loading) return <p>Učitavanje...</p>;
  if (!user) return <p>Nije moguće učitati korisnika.</p>;

  return (
    <>
      {message && <p>{message}</p>}

      <div className="profile-layout">
        <div className="profile-card">
          <h3>Osnovni podaci</h3>

          <p><strong>Ime:</strong> {user.ime}</p>
          <p><strong>Prezime:</strong> {user.prezime}</p>

          <p>
            <strong>Datum rođenja:</strong> {formatDate(user.datum_rodjenja)}
          </p>

          <p>
            <strong>Pol:</strong> {formatPol(user.pol)}
          </p>

          <p>
            <strong>Tip korisnika:</strong> {formatTipKorisnika(user.tip_korisnika)}
          </p>

          {user.tip_korisnika === "domaci" && (
            <p><strong>JMBG:</strong> {user.jmbg}</p>
          )}

          {user.tip_korisnika === "strani" && (
            <>
              <p><strong>Broj pasoša:</strong> {user.broj_pasosa}</p>
              <p><strong>Državljanstvo:</strong> {user.drzavljanstvo}</p>
            </>
          )}

          {user.tip_korisnika === "admin" && (
            <p><strong>Broj zaposlenog:</strong> {user.broj_zaposlenog}</p>
          )}

          {user.profile_photo_path && (
            <img
              src={`http://localhost:8000/storage/${user.profile_photo_path}`}
              alt="Profil"
              width="120"
              style={{ borderRadius: "10px", marginTop: "10px" }}
            />
          )}
        </div>

        <div className="profile-card">
          <h3>Izmena podataka</h3>

          <form onSubmit={handleSubmit}>
            <label>Email:</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
            />
            {errors.email && <p>{errors.email[0]}</p>}

            <label>Profilna slika:</label>
            <input type="file" onChange={handlePhotoChange} />

            {preview && (
              <img
                src={preview}
                alt="Preview"
                width="120"
                style={{ marginTop: "10px" }}
              />
            )}

            <hr />

            <h3>Promena lozinke</h3>

            <label>Trenutna lozinka:</label>
            <input
              type="password"
              value={currentPassword}
              onChange={(e) => setCurrentPassword(e.target.value)}
            />
            {errors.current_password && <p>{errors.current_password[0]}</p>}

            <label>Nova lozinka:</label>
            <input
              type="password"
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
            />
            {errors.new_password && <p>{errors.new_password[0]}</p>}

            <label>Potvrdi novu lozinku:</label>
            <input
              type="password"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
            />

            <button type="submit">Sačuvaj izmene</button>
          </form>
        </div>
      </div>
    </>
  );
}