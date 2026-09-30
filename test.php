import React, { useState, useEffect } from 'react';

// Embedded global styles to completely replace external CSS dependencies
const globalStyles = `
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background-color: #f8fafc;
    color: #1e293b;
    line-height: 1.5;
  }
  .app-container {
    background-color: #f8fafc;
    color: #334155;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  .header-sticky {
    position: sticky;
    top: 0;
    z-index: 50;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }
  .header-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1rem;
  }
  .header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 4rem;
  }
  .brand-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
  }
  .brand-badge {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #059669 0%, #14b8a6 50%, #34d399 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 900;
    font-size: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
  }
  .brand-name {
    font-size: 1.25rem;
    font-weight: 800;
    background: linear-gradient(to right, #059669, #0d9488);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .brand-tag {
    margin-left: 0.5rem;
    padding: 0.125rem 0.5rem;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    background-color: #d1fae5;
    color: #065f46;
    border-radius: 9999px;
    border: 1px solid #a7f3d0;
  }
  .nav-desktop {
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }
  .btn-nav {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    border: 1px solid transparent;
    cursor: pointer;
    background: transparent;
    color: #475569;
    transition: all 0.2s;
  }
  .btn-nav:hover {
    background-color: #f1f5f9;
    color: #059669;
  }
  .btn-nav.active {
    color: #047857;
    background-color: #ecfdf5;
    border-color: #a7f3d0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }
  .btn-nav.alert {
    color: #e11d48;
  }
  .btn-nav.alert:hover {
    background-color: #ffe4e6;
  }
  .cta-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .btn-community {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #334155;
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 0.75rem;
    text-decoration: none;
    transition: all 0.2s;
  }
  .btn-community:hover {
    background-color: #e2e8f0;
  }
  .btn-smart-offer {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 800;
    color: #ffffff;
    background: linear-gradient(to right, #f59e0b, #ea580c, #f43f5e);
    border-radius: 0.75rem;
    text-decoration: none;
    box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
    border: none;
    cursor: pointer;
  }
  .mobile-menu-btn {
    display: none;
    padding: 0.5rem;
    color: #475569;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
  }
  .mobile-dropdown {
    background-color: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 0.5rem 1rem 1rem 1rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  }
  .layout-wrapper {
    max-width: 1280px;
    margin: 2rem auto;
    padding: 0 1rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
    width: 100%;
  }
  @media (min-width: 1024px) {
    .layout-wrapper {
      flex-direction: row;
    }
    .main-content-area {
      width: 75%;
    }
    .sidebar-area {
      width: 25%;
    }
    .nav-desktop {
      display: flex;
    }
    .mobile-menu-btn {
      display: none;
    }
  }
  @media (max-width: 1023px) {
    .nav-desktop {
      display: none;
    }
    .mobile-menu-btn {
      display: block;
    }
    .btn-community-desktop {
      display: none;
    }
  }
  .card-box {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  .hero-banner {
    position: relative;
    overflow: hidden;
    border-radius: 1.5rem;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #022c22 100%);
    color: #ffffff;
    padding: 2.5rem 2rem;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
  }
  .smart-offer-card {
    margin: 2rem 0;
    padding: 2rem;
    background: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #e11d48 100%);
    border-radius: 1.5rem;
    color: #ffffff;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    align-items: center;
    justify-content: space-between;
  }
  @media (min-width: 768px) {
    .smart-offer-card {
      flex-direction: row;
    }
  }
  .ad-box {
    margin: 1.5rem 0;
    padding: 0.75rem;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    text-align: center;
    overflow: hidden;
  }
  .ad-tag {
    font-size: 0.625rem;
    text-transform: uppercase;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
  }
  .footer-wrapper {
    background-color: #0f172a;
    color: #94a3b8;
    border-top: 1px solid #1e293b;
    margin-top: 4rem;
    padding: 3rem 1rem;
    font-size: 0.875rem;
  }
  .ticker-box {
    background-color: #0f172a;
    color: #34d399;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .portal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
  }
  .iframe-container {
    width: 100%;
    height: 450px;
    border-radius: 1rem;
    border: 1px solid #cbd5e1;
    overflow: hidden;
  }
`;

export default function App() {
  const [currentRoute, setCurrentRoute] = useState('home');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [savedItems, setSavedItems] = useState([]);

  useEffect(() => {
    if (!document.getElementById('earnsmart-direct-styles')) {
      const styleTag = document.createElement('style');
      styleTag.id = 'earnsmart-direct-styles';
      styleTag.innerHTML = globalStyles;
      document.head.appendChild(styleTag);
    }

    if (!document.getElementById('font-awesome-cdn')) {
      const faLink = document.createElement('link');
      faLink.id = 'font-awesome-cdn';
      faLink.rel = 'stylesheet';
      faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
      document.head.appendChild(faLink);
    }

    const adcashLib = document.createElement('script');
    adcashLib.src = 'https://acscdn.com/script/aclib.js';
    adcashLib.async = true;
    adcashLib.onload = () => {
      try {
        if (window.aclib && typeof window.aclib.runAutoTag === 'function') {
          window.aclib.runAutoTag({ zoneId: "oxrrlp42ir" });
        }
      } catch (e) {
        console.error("Adcash Autotag initialization error:", e);
      }
    };
    document.head.appendChild(adcashLib);

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [currentRoute]);

  const toggleSaveItem = (title) => {
    if (savedItems.includes(title)) {
      setSavedItems(savedItems.filter((i) => i !== title));
    } else {
      setSavedItems([...savedItems, title]);
    }
  };

  const navigate = (route) => {
    setCurrentRoute(route);
    setMobileMenuOpen(false);
  };

  return (
    <div className="app-container">
      <header className="header-sticky">
        <div className="header-inner">
          <div className="header-row">
            <div className="brand-group" onClick={() => navigate('home')}>
              <div className="brand-badge">E$</div>
              <div>
                <span className="brand-name">EarnSmart</span>
                <span className="brand-tag">Verified Hub</span>
              </div>
            </div>

            <nav className="nav-desktop">
              <NavLink label="Home" active={currentRoute === 'home'} onClick={() => navigate('home')} icon="fa-house" />
              <NavLink label="App Portal & Activities" active={currentRoute === 'portal'} onClick={() => navigate('portal')} icon="fa-gamepad" />
              <NavLink label="App Reviews" active={currentRoute === 'apps'} onClick={() => navigate('apps')} icon="fa-mobile-screen-button" />
              <NavLink label="Side Hustles" active={currentRoute === 'hustles'} onClick={() => navigate('hustles')} icon="fa-briefcase" />
              <NavLink label="Scam Detector" active={currentRoute === 'scams'} onClick={() => navigate('scams')} icon="fa-shield-halved" isAlert={true} />
              <NavLink label="Calculator" active={currentRoute === 'calculator'} onClick={() => navigate('calculator')} icon="fa-calculator" />
            </nav>

            <div className="cta-group">
              <a 
                href="https://letstalk-akrf.onrender.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="btn-community btn-community-desktop"
              >
                <i className="fa-solid fa-comments" style={{ color: '#059669', marginRight: '0.375rem' }}></i> Let's Talk Community
              </a>
              <a 
                href="https://thatlobster.com/gp5hfpbsvx?key=f36330d2c264cb044f229cc4b04b2bea" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="btn-smart-offer"
              >
                <i className="fa-solid fa-star" style={{ marginRight: '0.375rem' }}></i> Smart Offer
              </a>

              <button 
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)} 
                className="mobile-menu-btn"
                aria-label="Toggle navigation menu"
              >
                {mobileMenuOpen ? <i className="fa-solid fa-xmark"></i> : <i className="fa-solid fa-bars"></i>}
              </button>
            </div>
          </div>
        </div>

        {mobileMenuOpen && (
          <div className="mobile-dropdown">
            <MobileNavButton label="Home Dashboard" onClick={() => navigate('home')} icon="fa-house" />
            <MobileNavButton label="App Portal & Activities" onClick={() => navigate('portal')} icon="fa-gamepad" />
            <MobileNavButton label="App Reviews" onClick={() => navigate('apps')} icon="fa-mobile-screen-button" />
            <MobileNavButton label="Side Hustle Guides" onClick={() => navigate('hustles')} icon="fa-briefcase" />
            <MobileNavButton label="Scam Detector Quiz" onClick={() => navigate('scams')} icon="fa-shield-halved" isAlert={true} />
            <MobileNavButton label="Earnings Calculator" onClick={() => navigate('calculator')} icon="fa-calculator" />
            <div style={{ paddingTop: '0.75rem', borderTop: '1px solid #f1f5f9', marginTop: '0.5rem', display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              <a 
                href="https://letstalk-akrf.onrender.com" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="btn-community"
                style={{ width: '100%', textAlign: 'center' }}
              >
                <i className="fa-solid fa-comments" style={{ color: '#059669', marginRight: '0.375rem' }}></i> Visit Let's Talk Platform
              </a>
            </div>
          </div>
        )}
      </header>

      <div className="layout-wrapper">
        <main className="main-content-area" style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
          {currentRoute === 'home' && <HomeView navigate={navigate} savedItems={savedItems} toggleSaveItem={toggleSaveItem} />}
          {currentRoute === 'portal' && <ActivitiesPortalView />}
          {currentRoute === 'apps' && <AppsView savedItems={savedItems} toggleSaveItem={toggleSaveItem} />}
          {currentRoute === 'hustles' && <HustlesView />}
          {currentRoute === 'scams' && <ScamDetectorQuizView />}
          {currentRoute === 'calculator' && <EarningsCalculatorView />}
        </main>

        <aside className="sidebar-area" style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          <SidebarWidget navigate={navigate} savedCount={savedItems.length} />
        </aside>
      </div>

      <Footer navigate={navigate} />
    </div>
  );
}

function NavLink({ label, active, onClick, isAlert, icon }) {
  return (
    <button 
      onClick={onClick} 
      className={`btn-nav ${active ? 'active' : ''} ${isAlert ? 'alert' : ''}`}
    >
      <i className={`fa-solid ${icon}`}></i>
      <span>{label}</span>
    </button>
  );
}

function MobileNavButton({ label, onClick, isAlert, icon }) {
  return (
    <button 
      onClick={onClick} 
      style={{
        width: '100%',
        textAlign: 'left',
        padding: '0.625rem 0.75rem',
        borderRadius: '0.75rem',
        fontSize: '0.875rem',
        fontWeight: '600',
        display: 'flex',
        alignItems: 'center',
        gap: '0.625rem',
        border: 'none',
        background: isAlert ? '#fff1f2' : 'transparent',
        color: isAlert ? '#e11d48' : '#334155',
        cursor: 'pointer',
        marginBottom: '0.25rem'
      }}
    >
      <i className={`fa-solid ${icon}`} style={{ color: isAlert ? '#e11d48' : '#64748b' }}></i>
      <span>{label}</span>
    </button>
  );
}

// Integrated Interactive Platform Gateway
function ActivitiesPortalView() {
  const [activeTab, setActiveTab] = useState('gaming');
  const [points, setPoints] = useState(120);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
      <div className="card-box" style={{ background: 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)', color: '#ffffff' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem' }}>
          <div>
            <span style={{ fontSize: '0.75rem', color: '#34d399', fontWeight: 'bold', textTransform: 'uppercase' }}>
              <i className="fa-solid fa-cubes"></i> Activity Ecosystem
            </span>
            <h2 style={{ fontSize: '1.75rem', fontWeight: 'bold', marginTop: '0.25rem' }}>Integrated Feature Portal</h2>
            <p style={{ color: '#94a3b8', fontSize: '0.875rem', marginTop: '0.5rem' }}>
              Participate in built-in mini-games, complete quick surveys, or code online to boost your daily streak.
            </p>
          </div>
          <div style={{ backgroundColor: '#334155', padding: '0.75rem 1.25rem', borderRadius: '1rem', border: '1px solid #475569' }}>
            <span style={{ fontSize: '0.75rem', color: '#cbd5e1' }}>Earned Portal XP</span>
            <div style={{ fontSize: '1.5rem', fontWeight: '900', color: '#f59e0b' }}>⭐ {points} XP</div>
          </div>
        </div>

        <div style={{ display: 'flex', gap: '0.75rem', marginTop: '1.5rem', flexWrap: 'wrap' }}>
          <button 
            onClick={() => setActiveTab('gaming')} 
            style={{ padding: '0.5rem 1rem', borderRadius: '0.5rem', border: 'none', background: activeTab === 'gaming' ? '#10b981' : '#334155', color: '#ffffff', cursor: 'pointer', fontWeight: 'bold' }}>
            <i className="fa-solid fa-gamepad"></i> Gaming Arena
          </button>
          <button 
            onClick={() => setActiveTab('editor')} 
            style={{ padding: '0.5rem 1rem', borderRadius: '0.5rem', border: 'none', background: activeTab === 'editor' ? '#10b981' : '#334155', color: '#ffffff', cursor: 'pointer', fontWeight: 'bold' }}>
            <i className="fa-solid fa-code"></i> Live Web Editor
          </button>
          <button 
            onClick={() => setActiveTab('surveys')} 
            style={{ padding: '0.5rem 1rem', borderRadius: '0.5rem', border: 'none', background: activeTab === 'surveys' ? '#10b981' : '#334155', color: '#ffffff', cursor: 'pointer', fontWeight: 'bold' }}>
            <i className="fa-solid fa-list-check"></i> Instant Survey Engine
          </button>
        </div>
      </div>

      {activeTab === 'gaming' && (
        <div className="card-box">
          <h3 style={{ fontSize: '1.25rem', fontWeight: 'bold', marginBottom: '0.5rem' }}>HTML5 Web Arcade & Gamified Rewards</h3>
          <p style={{ fontSize: '0.875rem', color: '#64748b', marginBottom: '1.5rem' }}>
            Test responsive HTML5 web games hosted directly on open-source platforms.
          </p>
          <div className="iframe-container">
            <iframe 
              src="https://gamedistribution.com/games/2048" 
              title="Embedded Game Platform" 
              style={{ width: '100%', height: '100%', border: 'none' }}
            ></iframe>
          </div>
          <button 
            onClick={() => setPoints(points + 15)} 
            style={{ marginTop: '1rem', padding: '0.75rem 1.25rem', backgroundColor: '#059669', color: '#ffffff', border: 'none', borderRadius: '0.5rem', fontWeight: 'bold', cursor: 'pointer' }}>
            <i className="fa-solid fa-gift"></i> Claim +15 XP Game Session Reward
          </button>
        </div>
      )}

      {activeTab === 'editor' && (
        <div className="card-box">
          <h3 style={{ fontSize: '1.25rem', fontWeight: 'bold', marginBottom: '0.5rem' }}>Online Interactive Code Sandbox</h3>
          <p style={{ fontSize: '0.875rem', color: '#64748b', marginBottom: '1.5rem' }}>
            Test frontend code snippets and build side projects using this embedded JSFiddle workspace.
          </p>
          <div className="iframe-container">
            <iframe 
              src="https://jsfiddle.net/boilerplate/react/embedded/result,js,html,css/dark/" 
              title="Embedded Web Editor" 
              style={{ width: '100%', height: '100%', border: 'none' }}
            ></iframe>
          </div>
        </div>
      )}

      {activeTab === 'surveys' && (
        <div className="card-box">
          <h3 style={{ fontSize: '1.25rem', fontWeight: 'bold', marginBottom: '1rem' }}>Instant Community Poll & Micro Survey</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <p style={{ fontSize: '0.875rem', fontWeight: '600' }}>Which category of online micro-tasks generates your highest monthly payout?</p>
            {['Software Testing & Bug Bounty', 'AI Prompt Evaluation & Data Annotation', 'Freelance Copywriting / Editing', 'Micro-Surveys & Offerwalls'].map((option, idx) => (
              <button 
                key={idx}
                onClick={() => setPoints(points + 20)}
                style={{ textAlign: 'left', padding: '0.75rem 1rem', borderRadius: '0.5rem', border: '1px solid #cbd5e1', backgroundColor: '#f8fafc', cursor: 'pointer', fontWeight: '500' }}>
                <i className="fa-regular fa-circle-check" style={{ marginRight: '0.5rem', color: '#10b981' }}></i> {option}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

function LiveActivityTicker() {
  const [tickerIndex, setTickerIndex] = useState(0);
  const activities = [
    "Sarah M. claimed a $25 PayPal payout from Freecash (3 mins ago)",
    "David K. submitted a scam report for 'CryptoTask-HQ' (12 mins ago)",
    "Elena R. earned $40 editing AI blog posts via Fiverr (28 mins ago)",
    "Michael B. verified minimum withdrawal proof for Swagbucks (41 mins ago)"
  ];

  useEffect(() => {
    const timer = setInterval(() => {
      setTickerIndex((prev) => (prev + 1) % activities.length);
    }, 4000);
    return () => clearInterval(timer);
  }, [activities.length]);

  return (
    <div className="ticker-box">
      <i className="fa-solid fa-bolt" style={{ color: '#f59e0b' }}></i>
      <span>LIVE AUDIT: {activities[tickerIndex]}</span>
    </div>
  );
}

function VastVideoPlayer({ zoneId = "12219894" }) {
  const [isPlaying, setIsPlaying] = useState(false);

  return (
    <div className="card-box" style={{ textAlign: 'center', background: '#0f172a', color: '#ffffff' }}>
      <div style={{ fontSize: '0.625rem', color: '#34d399', fontWeight: '800', textTransform: 'uppercase', marginBottom: '0.5rem' }}>
        <i className="fa-solid fa-circle-play" style={{ marginRight: '0.25rem' }}></i> Featured Video Research Spotlight
      </div>
      <h3 style={{ fontSize: '1.125rem', fontWeight: 'bold', marginBottom: '0.75rem' }}>How Real Earners Avoid Survey Trap Loops</h3>

      <div style={{ position: 'relative', width: '100%', aspectRatio: '16/9', backgroundColor: '#020617', borderRadius: '0.75rem', overflow: 'hidden', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        {!isPlaying ? (
          <div style={{ textAlign: 'center', padding: '1rem' }}>
            <button 
              onClick={() => setIsPlaying(true)}
              style={{ padding: '0.875rem 1.5rem', backgroundColor: '#10b981', color: '#ffffff', border: 'none', borderRadius: '9999px', fontWeight: 'bold', cursor: 'pointer', fontSize: '0.875rem', display: 'inline-flex', alignItems: 'center', gap: '0.5rem' }}
            >
              <i className="fa-solid fa-play"></i> Watch Video Guide
            </button>
            <p style={{ fontSize: '0.625rem', color: '#94a3b8', marginTop: '0.75rem' }}>Includes VAST ad-supported stream preview (Zone: {zoneId})</p>
          </div>
        ) : (
          <div style={{ width: '100%', height: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '1rem' }}>
            <p style={{ fontSize: '0.875rem', color: '#34d399' }}><i className="fa-solid fa-spinner fa-spin"></i> Loading VAST Tag Link Video Stream...</p>
            <button onClick={() => setIsPlaying(false)} style={{ marginTop: '1rem', background: 'transparent', color: '#94a3b8', border: '1px solid #334155', padding: '0.25rem 0.75rem', borderRadius: '0.5rem', cursor: 'pointer', fontSize: '0.75rem' }}>Close Video</button>
          </div>
        )}
      </div>
    </div>
  );
}

function SmartLinkBanner() {
  return (
    <div className="smart-offer-card">
      <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
        <span style={{
          padding: '0.25rem 0.75rem',
          backgroundColor: 'rgba(255, 255, 255, 0.2)',
          color: '#ffffff',
          borderRadius: '9999px',
          fontSize: '0.625rem',
          fontWeight: '900',
          textTransform: 'uppercase',
          letterSpacing: '0.05em',
          display: 'inline-block',
          width: 'fit-content'
        }}>
          <i className="fa-solid fa-circle-check" style={{ marginRight: '0.25rem', color: '#6ee7b7' }}></i> Verified Special Partner Offer
        </span>
        <h3 style={{ fontSize: '1.5rem', fontWeight: '900', letterSpacing: '-0.025em' }}>Unlock Premium High-Yield Portal Access</h3>
        <p style={{ color: '#fef3c7', fontSize: '0.875rem', maxWidth: '36rem' }}>
          Join over 12,000 verified online earners utilizing our exclusive partner gateway with guaranteed zero-fee payouts and instant setup.
        </p>
      </div>
      <a 
        href="https://thatlobster.com/gp5hfpbsvx?key=f36330d2c264cb044f229cc4b04b2bea" 
        target="_blank" 
        rel="noopener noreferrer" 
        style={{
          padding: '0.875rem 1.75rem',
          backgroundColor: '#ffffff',
          color: '#ea580c',
          fontWeight: '900',
          borderRadius: '1rem',
          textDecoration: 'none',
          whiteSpace: 'nowrap',
          display: 'inline-flex',
          alignItems: 'center',
          gap: '0.5rem',
          fontSize: '0.875rem',
          boxShadow: '0 10px 15px -3px rgba(0,0,0,0.2)'
        }}
      >
        <span>Claim Smart Offer Now</span>
        <i className="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  );
}

function AdBanner({ keyId, width, height, label }) {
  const containerId = `ad-container-${keyId}`;

  useEffect(() => {
    try {
      window.atOptions = {
        'key': keyId,
        'format': 'iframe',
        'height': height,
        'width': width,
        'params': {}
      };
      const script = document.createElement('script');
      script.src = `https://thatlobster.com/${keyId}/invoke.js`;
      script.async = true;
      const container = document.getElementById(containerId);
      if (container && container.childNodes.length === 0) {
        container.appendChild(script);
      }
    } catch (e) {
      console.error(`Ad loading error for key ${keyId}:`, e);
    }
  }, [keyId, height, width, containerId]);

  return (
    <div className="ad-box">
      <div className="ad-tag">
        <span>{label || `Sponsored Ad (${width}x${height})`}</span>
      </div>
      <div id={containerId} style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', width: '100%', minHeight: '50px', overflowX: 'auto' }}></div>
    </div>
  );
}

function DailyStreakWidget() {
  const [streak, setStreak] = useState(3);
  const [checked, setChecked] = useState(false);

  const handleCheckIn = () => {
    if (!checked) {
      setStreak(streak + 1);
      setChecked(true);
    }
  };

  return (
    <div className="card-box" style={{ background: 'linear-gradient(135deg, #059669 0%, #0d9488 100%)', color: '#ffffff' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h4 style={{ fontSize: '0.875rem', fontWeight: 'bold' }}>Daily Earning Habit Tracker</h4>
          <p style={{ fontSize: '0.75rem', opacity: 0.9 }}>Log in daily to optimize your earning potential</p>
        </div>
        <div style={{ fontSize: '1.25rem', fontWeight: '900', background: 'rgba(255,255,255,0.2)', padding: '0.375rem 0.75rem', borderRadius: '0.75rem' }}>
          🔥 {streak} Days
        </div>
      </div>
      <button 
        onClick={handleCheckIn}
        disabled={checked}
        style={{
          marginTop: '0.75rem',
          width: '100%',
          padding: '0.5rem',
          borderRadius: '0.5rem',
          border: 'none',
          backgroundColor: checked ? '#a7f3d0' : '#ffffff',
          color: checked ? '#065f46' : '#047857',
          fontWeight: 'bold',
          cursor: checked ? 'default' : 'pointer',
          fontSize: '0.75rem'
        }}
      >
        {checked ? '✓ Streak Updated for Today!' : 'Claim Daily Streak + Check In'}
      </button>
    </div>
  );
}

function SidebarWidget({ navigate, savedCount }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem', position: 'sticky', top: '5rem' }}>
      <DailyStreakWidget />

      <div style={{
        backgroundColor: '#0f172a',
        color: '#ffffff',
        borderRadius: '1.5rem',
        padding: '1.5rem',
        boxShadow: '0 10px 15px -3px rgba(0,0,0,0.1)',
        border: '1px solid #334155',
        display: 'flex',
        flexDirection: 'column',
        gap: '1rem'
      }}>
        <div style={{
          width: '2.5rem',
          height: '2.5rem',
          borderRadius: '0.75rem',
          backgroundColor: 'rgba(16, 185, 129, 0.2)',
          color: '#34d399',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '1.25rem',
          fontWeight: 'bold',
          border: '1px solid rgba(16, 185, 129, 0.3)'
        }}>
          <i className="fa-solid fa-bookmark"></i>
        </div>
        <div>
          <h4 style={{ fontSize: '1rem', fontWeight: 'bold', color: '#ffffff' }}>Saved Reading List</h4>
          <p style={{ color: '#cbd5e1', fontSize: '0.75rem', marginTop: '0.25rem', lineHeight: '1.5' }}>
            You have {savedCount} saved guides/reviews stored for offline reference.
          </p>
        </div>
      </div>

      <AdBanner keyId="2abdc436c63b9d82afd41c3c5fcd0a32" width={300} height={250} label="Sidebar Partner Ad (300x250)" />
      <AdBanner keyId="cd3fe29d9bf9ad57823e968b578a4d7e" width={160} height={600} label="Vertical Skyscraper (160x600)" />
    </div>
  );
}

function HomeView({ navigate, savedItems, toggleSaveItem }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '2.5rem' }}>
      <LiveActivityTicker />

      <div className="hero-banner">
        <div style={{ maxWidth: '42rem', display: 'flex', flexDirection: 'column', gap: '1.25rem' }}>
          <div style={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: '0.5rem',
            padding: '0.25rem 0.75rem',
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            border: '1px solid rgba(16, 185, 129, 0.3)',
            borderRadius: '9999px',
            color: '#6ee7b7',
            fontSize: '0.75rem',
            fontWeight: '600',
            width: 'fit-content'
          }}>
            <i className="fa-solid fa-shield-check"></i>
            <span>Verified Independent Earning Intelligence</span>
          </div>
          <h1 style={{ fontSize: '2.25rem', fontWeight: '900', lineHeight: '1.2' }}>
            Build Genuine Online Income with <span style={{ color: '#34d399' }}>Total Transparency</span>.
          </h1>
          <p style={{ color: '#cbd5e1', fontSize: '0.875rem', lineHeight: '1.6' }}>
            Eliminate hours wasted on fake task sites, low-paying survey traps, and predatory schemes. We audit side hustle platforms, micro-gig apps, and remote positions so you only invest time where it pays.
          </p>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.75rem', paddingTop: '0.5rem' }}>
            <button 
              onClick={() => navigate('portal')} 
              style={{
                padding: '0.875rem 1.5rem',
                backgroundColor: '#059669',
                color: '#ffffff',
                fontWeight: 'bold',
                borderRadius: '1rem',
                border: 'none',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem',
                fontSize: '0.875rem'
              }}
            >
              <span>Explore Interactive Features</span>
              <i className="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>

      <VastVideoPlayer zoneId="12219894" />
      <AdBanner keyId="77037cdd333d77b9b07c4dd401fc320b" width={728} height={90} label="Top Leaderboard Banner (728x90)" />
      <SmartLinkBanner />
    </div>
  );
}

function AppsView() {
  return (
    <div className="card-box">
      <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>App Reviews View</h2>
      <p style={{ marginTop: '0.5rem', color: '#64748b' }}>Explore verified apps and platform reviews.</p>
    </div>
  );
}

function HustlesView() {
  return (
    <div className="card-box">
      <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>Side Hustles View</h2>
      <p style={{ marginTop: '0.5rem', color: '#64748b' }}>Detailed guides on remote work, freelance writing, and micro-SaaS.</p>
    </div>
  );
}

function ScamDetectorQuizView() {
  return (
    <div className="card-box">
      <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>Scam Detector Quiz</h2>
      <p style={{ marginTop: '0.5rem', color: '#64748b' }}>Take quick quizzes to test if an online earning platform is legitimate.</p>
    </div>
  );
}

function EarningsCalculatorView() {
  return (
    <div className="card-box">
      <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>Earnings Calculator</h2>
      <p style={{ marginTop: '0.5rem', color: '#64748b' }}>Estimate potential hourly and monthly earnings based on platform effort.</p>
    </div>
  );
}

function Footer({ navigate }) {
  return (
    <footer className="footer-wrapper">
      <div style={{ maxWidth: '1280px', margin: '0 auto', textAlign: 'center' }}>
        <p>© {new Date().getFullYear()} EarnSmart Hub. All rights reserved.</p>
      </div>
    </footer>
  );
}
