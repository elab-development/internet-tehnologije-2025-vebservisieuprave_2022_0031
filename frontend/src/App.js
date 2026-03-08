
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
import AdminPanel from './pages/AdminPanel';
import { useState} from "react";
import UsersPanelAdmin from './pages/UsersPanelAdmin';
import UserProfileAdmin from './pages/UserProfileAdmin';
import UnprocessedRequestsPage from './pages/UnprocessedRequestsPage';
import RequestDetailsAdmin from "./pages/RequestDetailsAdmin";
 const getUserFromStorage = () => {
   try {
     const raw = localStorage.getItem("user");
     return raw ? JSON.parse(raw) : null;
   } catch {
     return null;
   }
 };


//ako zelimo da NavBar bude na svakoj stranici, navodimo ga ovde
function App() {
  const user = getUserFromStorage();
  return (
    <BrowserRouter>
    <NavBar></NavBar>
      <Routes>
       <Route path="/" element={<Pocetna user={user} />} />
        <Route path="/login" element={<LogInPage setUser={user} />} />
        <Route path="/userpage" element={<UserPage user={user} />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/mojizahtevi" element={<MojiZahtevi />} /> 
         <Route path="/zakazi-termin" element={<ZakaziTerminForm user={user} />} />
        <Route path="/admin/statistika" element={<AdminPanel user={user} />} />
        <Route path="/admin/korisnici" element={<UsersPanelAdmin user={user} />} />
        <Route path="/admin/korisnici/:id" element={<UserProfileAdmin user={user}/>} />
        <Route path="/admin/neobradjeniZahtevi" element={<UnprocessedRequestsPage user={user}/>} />
        <Route path="/admin/request/:id" element={<RequestDetailsAdmin user={user}/>} />

      </Routes>
      <Footer/>
    </BrowserRouter>
  );
}

export default App;


