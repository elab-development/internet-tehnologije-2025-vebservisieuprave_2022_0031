
import './App.css';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Pocetna from './pages/HomePage';
import { LogInPage } from './pages/LogInPage';
import NavBar from './components/NavBar';
import Footer from './components/Footer';

//ako zelimo da NavBar bude na svakoj stranici, navodimo ga ovde
function App() {
  return (
    <BrowserRouter>
    <NavBar></NavBar>
      <Routes>
        <Route path="/" element={<Pocetna />} />
        <Route path="/login" element={<LogInPage />} />
        {/*
        <Route path="/register" element={<RegisterPage />} />
        */}
      </Routes>
      <Footer/>
    </BrowserRouter>
  );
}

export default App;


