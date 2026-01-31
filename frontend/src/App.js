
import './App.css';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import Pocetna from './pages/Pocetna';
import { LogInPage } from './pages/LogInPage';
import NavBar from './components/NavBar';
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
    </BrowserRouter>
  );
}

export default App;


