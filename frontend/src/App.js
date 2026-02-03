
import './App.css';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Pocetna from './pages/HomePage';
import { LogInPage } from './pages/LogInPage';
import NavBar from './components/NavBar';
import Footer from './components/Footer';
import UserPage from './pages/UserPage';
import Register from './pages/Register';
import MojiZahtevi from './pages/MojiZahtevi';
import ZakaziTerminForm from './pages/ZakaziTerminForm';

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
        <Route path="/" element={<Pocetna />} />
        <Route path="/login" element={<LogInPage />} />
        <Route path="/userpage" element={<UserPage />} />
        <Route path="/register" element={<Register />} />
         <Route path="/mojizahtevi" element={<MojiZahtevi />} />
          <Route path="/zakazi-termin" element={<ZakaziTerminForm user={user} />} />

      </Routes>
      <Footer/>
    </BrowserRouter>
  );
}

export default App;


