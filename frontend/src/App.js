
import './App.css';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Pocetna from './pages/HomePage';
import { LogInPage } from './pages/LogInPage';
import NavBar from './components/NavBar';
import Footer from './components/Footer';
import UserPage from './pages/UserPage';
import RegisterPage from './pages/RegisterPage';
import MojiZahtevi from './pages/MojiZahtevi';
import ZakaziTerminForm from './pages/ZakaziTerminForm';
import { useState} from "react";
// const getUserFromStorage = () => {
//   try {
//     const raw = localStorage.getItem("user");
//     return raw ? JSON.parse(raw) : null;
//   } catch {
//     return null;
//   }
// };


//ako zelimo da NavBar bude na svakoj stranici, navodimo ga ovde
function App() {
  const [user, setUser] = useState(() => {
    const raw = localStorage.getItem("user");
    return raw ? JSON.parse(raw) : null;
  });
  return (
    <BrowserRouter>
    <NavBar user={user} setUser={setUser} />
      <Routes>
        <Route path="/" element={<Pocetna />} />
        <Route path="/login" element={<LogInPage setUser={setUser} />} />
        <Route path="/userpage" element={<UserPage user={user} />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/mojizahtevi" element={<MojiZahtevi />} /> 
         <Route path="/zakazi-termin" element={<ZakaziTerminForm user={user} />} />
        <Route path="/profil" element={<UserPage />} />

      </Routes>
      <Footer/>
    </BrowserRouter>
  );
}

export default App;


