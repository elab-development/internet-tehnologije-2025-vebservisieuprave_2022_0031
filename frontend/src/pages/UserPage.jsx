import { useEffect, useState } from "react";
import axios from "axios";
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

  const token = localStorage.getItem("token");

  useEffect(() => {
    axios
      .get("http://localhost:8000/api/me", {
        headers: { Authorization: `Bearer ${token}` },
      })
      .then((res) => {
        setUser(res.data);
        setEmail(res.data.email);
      })
      .catch(() => {
        setMessage("Greška pri učitavanju korisnika.");
      });
  }, []);

  const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    setPhoto(file);
    if (file) {
      setPreview(URL.createObjectURL(file));
    }
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
      const res = await axios.post(
        "http://localhost:8000/api/profile?_method=PUT",
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "multipart/form-data",
          },
        }
      );

      setMessage(res.data.message);
      setUser(res.data.user);

      setCurrentPassword("");
      setNewPassword("");
      setConfirmPassword("");
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setMessage(err.response?.data?.message || "Greška.");
      }
    }
  };

  if (!user) return <p>Učitavanje...</p>;

  return (
    <div className="profile-container">
      <h2>Profil korisnika</h2>

      {message && <p>{message}</p>}

      <form onSubmit={handleSubmit}>
        <h3>Osnovni podaci</h3>

        <p><strong>Ime:</strong> {user.ime}</p>
        <p><strong>Prezime:</strong> {user.prezime}</p>
        <p><strong>Datum rođenja:</strong> {user.datum_rodjenja}</p>
        <p><strong>Pol:</strong> {user.pol}</p>
        <p><strong>Tip korisnika:</strong> {user.tip_korisnika}</p>

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

        <hr />

        <h3>Izmena podataka</h3>

        <label>Email:</label><br />
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        {errors.email && <p>{errors.email[0]}</p>}

        <br /><br />

        <label>Profilna slika:</label><br />
        <input type="file" onChange={handlePhotoChange} />

        <br /><br />

        {preview ? (
          <img src={preview} alt="Preview" width="120" />
        ) : user.profile_photo_path ? (
          <img
            src={`http://localhost:8000/storage/${user.profile_photo_path}`}
            alt="Profil"
            width="120"
          />
        ) : null}

        <hr />

        <h3>Promena lozinke</h3>

        <label>Trenutna lozinka:</label><br />
        <input
          type="password"
          value={currentPassword}
          onChange={(e) => setCurrentPassword(e.target.value)}
        />
        {errors.current_password && (
          <p>{errors.current_password[0]}</p>
        )}

        <br /><br />

        <label>Nova lozinka:</label><br />
        <input
          type="password"
          value={newPassword}
          onChange={(e) => setNewPassword(e.target.value)}
        />
        {errors.new_password && (
          <p>{errors.new_password[0]}</p>
        )}

        <br /><br />

        <label>Potvrdi novu lozinku:</label><br />
        <input
          type="password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
        />

        <br /><br />

        <button type="submit">Sačuvaj izmene</button>
      </form>
    </div>
  );
}