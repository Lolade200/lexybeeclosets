import React, { useState, useEffect, useRef } from 'react';

export default function App() {
  const [db, setDb] = useState(null);
  const [storage, setStorage] = useState(null);
  const [firebaseConnected, setFirebaseConnected] = useState(false);

  // Initialize user from localStorage so browser refreshes keep you logged in
  const [user, setUser] = useState(() => {
    try {
      const savedUser = localStorage.getItem('truetalknow_current_user');
      return savedUser ? JSON.parse(savedUser) : null;
    } catch (e) {
      return null;
    }
  });

  const [authView, setAuthView] = useState('login'); 

  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');
  const [signupEmail, setSignupEmail] = useState('');
  const [signupPassword, setSignupPassword] = useState('');
  const [signupMajor, setSignupMajor] = useState('Computer Science');
  const [signupYear, setSignupYear] = useState('Sophomore');

  const [activeTab, setActiveTab] = useState('feed'); 
  const [feedFilter, setFeedFilter] = useState('all'); 
  const [notification, setNotification] = useState(null);

  const [users, setUsers] = useState([
    { uid: 'u1', email: 'neon@uni.edu', password: 'Password1', codeName: 'A&33', studentCode: '#TALK-4821', major: 'Computer Science', year: 'Junior', avatarUrl: 'https://api.dicebear.com/7.x/bottts/svg?seed=A33' },
    { uid: 'u2', email: 'quantum@uni.edu', password: 'Password1', codeName: 'Q#91', studentCode: '#TALK-9102', major: 'Engineering', year: 'Senior', avatarUrl: 'https://api.dicebear.com/7.x/bottts/svg?seed=Q91' },
    { uid: 'u3', email: 'cyber@uni.edu', password: 'Password1', codeName: 'X$42', studentCode: '#TALK-3341', major: 'Fine Arts', year: 'Sophomore', avatarUrl: 'https://api.dicebear.com/7.x/bottts/svg?seed=X42' }
  ]);

  const [posts, setPosts] = useState([
    {
      id: 'p_platform_guide',
      authorUid: 'system_admin',
      authorCodeName: 'SYS#01',
      authorStudentCode: '#TALK-0001',
      authorMajor: 'Platform Core',
      authorAvatar: 'https://api.dicebear.com/7.x/bottts/svg?seed=SYS01',
      type: 'text',
      content: 'Welcome to truetalknow! You can now search users, follow peers, and view their specific profiles & posts. 🌟',
      mediaUrl: '',
      likes: ['u1', 'u2', 'u3'],
      createdAt: Date.now() - 3600000
    }
  ]);

  const [commentsMap, setCommentsMap] = useState({});
  const [messages, setMessages] = useState([]);
  const [follows, setFollows] = useState({}); // { [followerUid]: [targetUid1, targetUid2] }

  const [newPostType, setNewPostType] = useState('text'); 
  const [newPostContent, setNewPostContent] = useState('');
  const [mediaPreviewUrl, setMediaPreviewUrl] = useState('');
  const [selectedFile, setSelectedFile] = useState(null);
  const [customVideoChoice, setCustomVideoChoice] = useState('sample1');
  const fileInputRef = useRef(null);

  const [visibleCommentsPostId, setVisibleCommentsPostId] = useState(null);
  const [newCommentText, setNewCommentText] = useState('');

  const [activeChatUser, setActiveChatUser] = useState(null);
  const [newMessageText, setNewMessageText] = useState('');
  
  // Search / Profile Inspection states
  const [searchQuery, setSearchQuery] = useState('');
  const [inspectingUser, setInspectingUser] = useState(null);

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Keep localStorage updated whenever user state changes
  useEffect(() => {
    if (user) {
      localStorage.setItem('truetalknow_current_user', JSON.stringify(user));
    } else {
      localStorage.removeItem('truetalknow_current_user');
    }
  }, [user]);

  useEffect(() => {
    if (!document.getElementById('fa-cdn')) {
      const link = document.createElement('link');
      link.id = 'fa-cdn';
      link.rel = 'stylesheet';
      link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
      document.head.appendChild(link);
    }

    if (!document.getElementById('hide-scrollbar-style')) {
      const style = document.createElement('style');
      style.id = 'hide-scrollbar-style';
      style.innerHTML = `
        ::-webkit-scrollbar { display: none !important; width: 0px !important; background: transparent !important; }
        * { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        @keyframes pulseGlow { 0%, 100% { opacity: 0.4; transform: scale(1); } 50% { opacity: 0.7; transform: scale(1.05); } }
        .animate-glow { animation: pulseGlow 8s infinite ease-in-out; }
        .mobile-bottom-nav { display: none; }
        .desktop-nav { display: flex; }
        .mobile-only-header-toggle { display: none; }
        @media (max-width: 768px) {
          .mobile-bottom-nav { display: flex; }
          .desktop-nav { display: none !important; }
          .mobile-only-header-toggle { display: flex !important; }
          body { padding-bottom: 70px; }
        }
      `;
      document.head.appendChild(style);
    }

    const loadFirebaseScripts = () => {
      if (window.firebase) {
        initFirebaseApp();
        return;
      }
      const scriptApp = document.createElement('script');
      scriptApp.src = 'https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js';
      scriptApp.onload = () => {
        const scriptDb = document.createElement('script');
        scriptDb.src = 'https://www.gstatic.com/firebasejs/10.8.0/firebase-database-compat.js';
        scriptDb.onload = () => {
          const scriptStorage = document.createElement('script');
          scriptStorage.src = 'https://www.gstatic.com/firebasejs/10.8.0/firebase-storage-compat.js';
          scriptStorage.onload = initFirebaseApp;
          document.head.appendChild(scriptStorage);
        };
        document.head.appendChild(scriptDb);
      };
      document.head.appendChild(scriptApp);
    };

    const initFirebaseApp = () => {
      try {
        const firebaseConfig = {
          apiKey: "AIzaSyCmYlnwDUqcwS2LJ0fzVKfuSfcIDqXSzhw",
          authDomain: "truetalknow.firebaseapp.com",
          databaseURL: "https://truetalknow-default-rtdb.firebaseio.com",
          projectId: "truetalknow",
          storageBucket: "truetalknow.firebasestorage.app",
          messagingSenderId: "226548430503",
          appId: "1:226548430503:web:95f684c8f2d903d6f38d82",
          measurementId: "G-NHXE0MR09P"
        };
        if (!window.firebase.apps.length) {
          window.firebase.initializeApp(firebaseConfig);
        }
        const database = window.firebase.database();
        const storageInstance = window.firebase.storage();
        setDb(database);
        setStorage(storageInstance);
        setFirebaseConnected(true);
      } catch (err) {
        console.error("Firebase init error:", err);
      }
    };

    loadFirebaseScripts();
  }, []);

  useEffect(() => {
    if (!db) return;

    const usersRef = db.ref('truetalknow_users');
    usersRef.on('value', (snapshot) => {
      const data = snapshot.val();
      if (data) {
        const loadedUsers = Object.keys(data).map(key => ({ id: key, ...data[key] }));
        setUsers(prev => {
          const merged = [...prev];
          loadedUsers.forEach(lu => {
            if (!merged.some(m => m.email.toLowerCase() === lu.email.toLowerCase())) {
              merged.push(lu);
            }
          });
          return merged;
        });
      }
    });

    const postsRef = db.ref('truetalknow_posts');
    postsRef.on('value', (snapshot) => {
      const data = snapshot.val();
      if (data) {
        const loadedPosts = Object.keys(data).map(key => ({ id: key, ...data[key] }));
        const sorted = loadedPosts.sort((a, b) => b.createdAt - a.createdAt);
        setPosts(sorted);
      } else {
        setPosts([]);
      }
    });

    const commentsRef = db.ref('truetalknow_comments');
    commentsRef.on('value', (snapshot) => {
      const data = snapshot.val();
      if (data) {
        const formattedMap = {};
        Object.keys(data).forEach(postId => {
          const commentsObj = data[postId];
          formattedMap[postId] = Object.keys(commentsObj).map(cId => ({
            id: cId,
            ...commentsObj[cId]
          }));
        });
        setCommentsMap(formattedMap);
      } else {
        setCommentsMap({});
      }
    });

    const msgRef = db.ref('truetalknow_messages');
    msgRef.on('value', (snapshot) => {
      const data = snapshot.val();
      if (data) {
        const loadedMsgs = Object.keys(data).map(key => ({ id: key, ...data[key] }));
        setMessages(loadedMsgs);
      }
    });

    const followRef = db.ref('truetalknow_follows');
    followRef.on('value', (snapshot) => {
      const data = snapshot.val();
      if (data) {
        setFollows(data);
      }
    });
  }, [db]);

  const showToast = (msg) => {
    setNotification(msg);
    setTimeout(() => setNotification(null), 3500);
  };

  const generateCodeName = () => {
    const chars1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const chars2 = '!@#$%&*?';
    const chars3 = '0123456789';
    const chars4 = 'abcdefghijklmnopqrstuvwxyz';
    return `${chars1[Math.floor(Math.random() * chars1.length)]}${chars2[Math.floor(Math.random() * chars2.length)]}${chars3[Math.floor(Math.random() * chars3.length)]}${chars4[Math.floor(Math.random() * chars4.length)]}`;
  };

  const handleLoginSubmit = (e) => {
    e.preventDefault();
    if (!loginEmail.trim() || !loginPassword.trim()) {
      showToast("Please enter both email and password!");
      return;
    }
    const trimmedEmail = loginEmail.trim().toLowerCase();
    const existingUser = users.find(u => u.email.toLowerCase() === trimmedEmail);
    if (!existingUser) {
      showToast("Email not found in database! Please sign up first.");
      setAuthView('signup');
      setSignupEmail(loginEmail.trim());
      return;
    }
    if (existingUser.password !== loginPassword) {
      showToast("Incorrect password! Please check your credentials.");
      return;
    }
    setUser(existingUser);
    showToast(`Welcome back, node ${existingUser.codeName}! 🎉`);
  };

  const handleSignupSubmit = (e) => {
    e.preventDefault();
    if (!signupEmail.trim() || !signupPassword.trim()) {
      showToast("Please fill in all required fields!");
      return;
    }
    if (signupPassword.length < 6) {
      showToast("Password must be at least 6 characters long!");
      return;
    }
    const trimmedEmail = signupEmail.trim().toLowerCase();
    const emailTaken = users.some(u => u.email.toLowerCase() === trimmedEmail);
    if (emailTaken) {
      showToast("Email already registered! Please log in instead.");
      setAuthView('login');
      setLoginEmail(signupEmail.trim());
      return;
    }

    const uniqueNodeId = `node_${Math.random().toString(36).substring(2, 8)}_${Date.now()}`;
    const codeName = generateCodeName();
    const studentCode = `#TALK-${Math.floor(1000 + Math.random() * 9000)}`;
    const avatarUrl = `https://api.dicebear.com/7.x/bottts/svg?seed=${encodeURIComponent(codeName)}`;

    const newProfile = {
      uid: uniqueNodeId,
      email: signupEmail.trim(),
      password: signupPassword,
      codeName,
      studentCode,
      major: signupMajor,
      year: signupYear,
      avatarUrl,
      createdAt: Date.now()
    };

    setUsers(prev => [...prev, newProfile]);
    setUser(newProfile);

    if (db) {
      db.ref(`truetalknow_users/${uniqueNodeId}`).set(newProfile);
    }
    showToast(`Anonymous node generated: ${codeName} 🚀`);
  };

  const handleLogout = () => {
    setUser(null);
    localStorage.removeItem('truetalknow_current_user');
    setMobileMenuOpen(false);
    showToast("Successfully logged out.");
  };

  const handleToggleFollow = (targetUid) => {
    const myUid = user.uid;
    const currentList = follows[myUid] || [];
    const isFollowing = currentList.includes(targetUid);
    const updatedList = isFollowing
      ? currentList.filter(id => id !== targetUid)
      : [...currentList, targetUid];

    const newFollowsMap = { ...follows, [myUid]: updatedList };
    setFollows(newFollowsMap);

    if (db) {
      db.ref(`truetalknow_follows/${myUid}`).set(updatedList);
    }
    showToast(isFollowing ? "Unfollowed node." : "Now following node! 🌟");
  };

  const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 50 * 1024 * 1024) {
      showToast("File size too large! Please choose a file under 50MB.");
      return;
    }

    setSelectedFile(file);

    const reader = new FileReader();
    reader.onloadend = () => {
      setMediaPreviewUrl(reader.result);
      showToast("Media loaded successfully for preview! 📎");
    };
    reader.onerror = () => {
      showToast("Error reading file.");
    };
    reader.readAsDataURL(file);
  };

  const handleCreatePost = async (e) => {
    e.preventDefault();
    if (!newPostContent.trim() && !mediaPreviewUrl && !selectedFile) {
      showToast("Please write something or attach media!");
      return;
    }

    showToast("Uploading media to Firebase Storage...");

    let finalMediaUrl = mediaPreviewUrl;

    try {
      if (selectedFile && storage) {
        const fileRef = storage.ref(`truetalknow_media/${Date.now()}_${selectedFile.name}`);
        const snapshot = await fileRef.put(selectedFile);
        finalMediaUrl = await snapshot.ref.getDownloadURL();
      } else if (newPostType === 'reel' && !finalMediaUrl) {
        if (customVideoChoice === 'sample1') {
          finalMediaUrl = 'https://www.w3schools.com/html/mov_bbb.mp4';
        } else if (customVideoChoice === 'sample2') {
          finalMediaUrl = 'https://assets.mixkit.co/videos/preview/mixkit-tree-branches-in-the-breeze-1185-large.mp4';
        } else {
          finalMediaUrl = 'https://assets.mixkit.co/videos/preview/mixkit-waves-in-the-water-1164-large.mp4';
        }
      } else if (newPostType === 'image' && !finalMediaUrl) {
        finalMediaUrl = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1000&q=80';
      }

      const postId = `p_${Date.now()}`;
      const newPostData = {
        id: postId,
        authorUid: user.uid,
        authorCodeName: user.codeName,
        authorStudentCode: user.studentCode,
        authorMajor: user.major,
        authorAvatar: user.avatarUrl,
        type: newPostType,
        content: newPostContent,
        mediaUrl: finalMediaUrl,
        likes: [],
        createdAt: Date.now()
      };

      setPosts(prev => [newPostData, ...prev]);
      if (db) {
        await db.ref(`truetalknow_posts/${postId}`).set(newPostData);
      }

      setNewPostContent("");
      setMediaPreviewUrl("");
      setSelectedFile(null);
      setNewPostType("text");
      showToast("Whisper published to Firebase successfully! ✨");
    } catch (err) {
      console.error("Storage upload error:", err);
      showToast("Error uploading file to storage. Please try again.");
    }
  };

  const handleToggleLike = (post) => {
    const currentLikes = post.likes || [];
    const hasLiked = currentLikes.includes(user.uid);
    const updatedLikes = hasLiked
      ? currentLikes.filter(id => id !== user.uid)
      : [...currentLikes, user.uid];

    setPosts(prev => prev.map(p => p.id === post.id ? { ...p, likes: updatedLikes } : p));
    if (db) {
      db.ref(`truetalknow_posts/${post.id}/likes`).set(updatedLikes);
    }
  };

  const handleSharePost = (post) => {
    const shareText = `Check out this whisper on truetalknow by ${post.authorCodeName}: "${post.content ? post.content.substring(0, 80) + '...' : 'Media Post'}"`;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(shareText).then(() => {
        showToast("Whisper content copied to clipboard! 📋");
      }).catch(() => {
        showToast("Share link generated!");
      });
    } else {
      showToast("Whisper shared successfully!");
    }
  };

  const handleDeletePost = (postId) => {
    setPosts(prev => prev.filter(p => p.id !== postId));
    if (db) {
      db.ref(`truetalknow_posts/${postId}`).remove();
      db.ref(`truetalknow_comments/${postId}`).remove();
    }
    showToast("Whisper deleted successfully.");
  };

  const handleAddComment = (e, postId) => {
    e.preventDefault();
    if (!newCommentText.trim()) return;

    const commentId = `c_${Date.now()}`;
    const commentData = {
      id: commentId,
      authorCodeName: user.codeName,
      authorStudentCode: user.studentCode,
      authorAvatar: user.avatarUrl,
      text: newCommentText.trim(),
      createdAt: Date.now()
    };

    setCommentsMap(prev => ({
      ...prev,
      [postId]: [...(prev[postId] || []), commentData]
    }));

    if (db) {
      db.ref(`truetalknow_comments/${postId}/${commentId}`).set(commentData);
    }
    setNewCommentText("");
    showToast("Comment added!");
  };

  const handleSendMessage = (e) => {
    e.preventDefault();
    if (!newMessageText.trim() || !activeChatUser) return;

    const msgId = `m_${Date.now()}`;
    const msgData = {
      id: msgId,
      senderUid: user.uid,
      receiverUid: activeChatUser.uid,
      text: newMessageText.trim(),
      createdAt: Date.now()
    };

    setMessages(prev => [...prev, msgData]);
    if (db) {
      db.ref(`truetalknow_messages/${msgId}`).set(msgData);
    }
    setNewMessageText("");
  };

  const styles = {
    container: {
      minHeight: '100vh',
      backgroundColor: '#030712',
      color: '#f9fafb',
      fontFamily: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
      display: 'flex',
      flexDirection: 'column',
      position: 'relative',
      overflowX: 'hidden',
      width: '100%',
      boxSizing: 'border-box'
    },
    glow1: {
      position: 'absolute',
      top: '-15%',
      left: '-10%',
      width: 'min(600px, 80vw)',
      height: 'min(600px, 80vw)',
      backgroundColor: 'rgba(14, 165, 233, 0.12)',
      borderRadius: '50%',
      filter: 'blur(140px)',
      pointerEvents: 'none',
      zIndex: 0
    },
    glow2: {
      position: 'absolute',
      bottom: '-15%',
      right: '-10%',
      width: 'min(600px, 80vw)',
      height: 'min(600px, 80vw)',
      backgroundColor: 'rgba(168, 85, 247, 0.12)',
      borderRadius: '50%',
      filter: 'blur(140px)',
      pointerEvents: 'none',
      zIndex: 0
    },
    toast: {
      position: 'fixed',
      top: '24px',
      left: '50%',
      transform: 'translateX(-50%)',
      zIndex: 100,
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      border: '1px solid rgba(56, 189, 248, 0.3)',
      color: '#f8fafc',
      padding: '12px 24px',
      borderRadius: '16px',
      boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.7)',
      display: 'flex',
      alignItems: 'center',
      gap: '12px',
      backdropFilter: 'blur(16px)',
      fontSize: '13px',
      fontWeight: 500,
      maxWidth: '92%',
      textAlign: 'center'
    },
    header: {
      position: 'sticky',
      top: 0,
      zIndex: 50,
      backgroundColor: 'rgba(3, 7, 18, 0.9)',
      backdropFilter: 'blur(20px)',
      borderBottom: '1px solid rgba(31, 41, 55, 0.8)',
      width: '100%',
      boxSizing: 'border-box'
    },
    headerInner: {
      maxWidth: '1100px',
      margin: '0 auto',
      padding: '0 16px',
      height: '70px',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      boxSizing: 'border-box',
      gap: '8px'
    },
    logoBox: {
      display: 'flex',
      alignItems: 'center',
      gap: '10px',
      minWidth: 0,
      flexShrink: 0
    },
    uniqueChatLogo: {
      width: '42px',
      height: '42px',
      borderRadius: '14px',
      background: 'linear-gradient(135deg, #0ea5e9, #6366f1, #ec4899)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      boxShadow: '0 10px 25px -5px rgba(236, 72, 153, 0.4)',
      color: '#fff',
      flexShrink: 0,
      fontSize: '18px',
      position: 'relative',
      border: '1px solid rgba(255, 255, 255, 0.2)'
    },
    logoTitle: {
      fontSize: '16px',
      fontWeight: 800,
      color: '#fff',
      display: 'flex',
      alignItems: 'center',
      gap: '6px',
      margin: 0,
      letterSpacing: '-0.025em',
      whiteSpace: 'nowrap',
      overflow: 'hidden',
      textOverflow: 'ellipsis'
    },
    badge: {
      fontSize: '10px',
      backgroundColor: 'rgba(14, 165, 233, 0.15)',
      color: '#38bdf8',
      padding: '2px 6px',
      borderRadius: '9999px',
      border: '1px solid rgba(14, 165, 233, 0.3)',
      fontWeight: 700
    },
    nav: {
      display: 'flex',
      alignItems: 'center',
      gap: '4px',
      backgroundColor: 'rgba(17, 24, 39, 0.7)',
      padding: '4px',
      borderRadius: '16px',
      border: '1px solid rgba(55, 65, 81, 0.5)'
    },
    navBtn: (active) => ({
      display: 'flex',
      alignItems: 'center',
      gap: '6px',
      padding: '8px 12px',
      borderRadius: '12px',
      fontSize: '12px',
      fontWeight: active ? 700 : 600,
      backgroundColor: active ? '#0ea5e9' : 'transparent',
      color: active ? '#030712' : '#9ca3af',
      border: 'none',
      cursor: 'pointer',
      boxShadow: active ? '0 10px 20px -5px rgba(14, 165, 233, 0.4)' : 'none',
      transition: 'all 0.25s ease',
      whiteSpace: 'nowrap'
    }),
    main: {
      flex: 1,
      maxWidth: '850px',
      width: '100%',
      margin: '0 auto',
      padding: '24px 16px',
      zIndex: 10,
      boxSizing: 'border-box'
    },
    card: {
      backgroundColor: 'rgba(17, 24, 39, 0.85)',
      border: '1px solid rgba(55, 65, 81, 0.6)',
      borderRadius: '24px',
      padding: '20px',
      boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.5)',
      backdropFilter: 'blur(16px)',
      marginBottom: '20px',
      boxSizing: 'border-box',
      width: '100%',
      transition: 'all 0.3s ease'
    },
    input: {
      width: '100%',
      backgroundColor: '#030712',
      border: '1px solid rgba(55, 65, 81, 0.8)',
      borderRadius: '16px',
      padding: '14px 16px',
      fontSize: '14px',
      color: '#fff',
      outline: 'none',
      boxSizing: 'border-box'
    },
    select: {
      width: '100%',
      backgroundColor: '#030712',
      border: '1px solid rgba(55, 65, 81, 0.8)',
      borderRadius: '16px',
      padding: '12px 14px',
      fontSize: '14px',
      color: '#fff',
      outline: 'none',
      boxSizing: 'border-box'
    },
    btnPrimary: {
      width: '100%',
      padding: '14px 20px',
      background: 'linear-gradient(135deg, #0ea5e9, #6366f1, #a855f7)',
      color: '#fff',
      fontWeight: 700,
      border: 'none',
      borderRadius: '16px',
      cursor: 'pointer',
      boxShadow: '0 10px 25px -5px rgba(14, 165, 233, 0.4)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: '10px',
      fontSize: '14px',
      boxSizing: 'border-box'
    },
    footer: {
      textAlign: 'center',
      padding: '24px',
      fontSize: '12px',
      color: '#6b7280',
      zIndex: 10
    }
  };

  if (!user) {
    return (
      <div style={styles.container}>
        <div style={styles.glow1} className="animate-glow"></div>
        <div style={styles.glow2} className="animate-glow"></div>

        {notification && (
          <div style={styles.toast}>
            <i className="fa-solid fa-bell" style={{ color: '#38bdf8' }}></i>
            <span>{notification}</span>
          </div>
        )}

        <header style={styles.header}>
          <div style={styles.headerInner}>
            <div style={styles.logoBox}>
              <div style={styles.uniqueChatLogo}>
                <i className="fa-solid fa-comments-dollar"></i>
                <span style={{ position: 'absolute', bottom: '-2px', right: '-2px', width: '10px', height: '10px', backgroundColor: '#34d399', borderRadius: '50%', border: '2px solid #030712' }}></span>
              </div>
              <div>
                <h1 style={styles.logoTitle}>
                  truetalknow <span style={styles.badge}>v3.5</span>
                </h1>
                <p style={{ fontSize: '10px', color: '#9ca3af', margin: 0 }}>Anonymous Campus Network</p>
              </div>
            </div>
          </div>
        </header>

        <main style={{ ...styles.main, maxWidth: '440px', margin: 'auto', padding: '24px' }}>
          {authView === 'login' ? (
            <div style={{ ...styles.card, padding: '28px' }}>
              <div style={{ textAlign: 'center', marginBottom: '24px' }}>
                <div style={{ width: '60px', height: '60px', borderRadius: '20px', background: 'linear-gradient(135deg, #0ea5e9, #6366f1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 14px', color: '#fff', fontSize: '24px' }}>
                  <i className="fa-solid fa-fingerprint"></i>
                </div>
                <h2 style={{ fontSize: '20px', fontWeight: 800, color: '#fff', margin: '0 0 6px 0' }}>Welcome Back</h2>
                <p style={{ fontSize: '13px', color: '#9ca3af', margin: 0 }}>Persistent session active</p>
              </div>

              <form onSubmit={handleLoginSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                <div>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '8px' }}>Campus Email</label>
                  <input 
                    type="email" 
                    required 
                    placeholder="student@university.edu"
                    value={loginEmail}
                    onChange={(e) => setLoginEmail(e.target.value)}
                    style={styles.input}
                  />
                </div>
                <div>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '8px' }}>Password</label>
                  <input 
                    type="password" 
                    required 
                    placeholder="••••••••"
                    value={loginPassword}
                    onChange={(e) => setLoginPassword(e.target.value)}
                    style={styles.input}
                  />
                </div>

                <button type="submit" style={{ ...styles.btnPrimary, marginTop: '8px' }}>
                  <span>Access Secure Node</span>
                  <i className="fa-solid fa-arrow-right"></i>
                </button>
              </form>

              <div style={{ textAlign: 'center', marginTop: '20px' }}>
                <button 
                  onClick={() => setAuthView('signup')}
                  style={{ background: 'none', border: 'none', color: '#38bdf8', fontSize: '12px', cursor: 'pointer', fontWeight: 600 }}
                >
                  Need an anonymous node? Sign up
                </button>
              </div>
            </div>
          ) : (
            <div style={{ ...styles.card, padding: '28px' }}>
              <div style={{ textAlign: 'center', marginBottom: '24px' }}>
                <div style={{ width: '60px', height: '60px', borderRadius: '20px', background: 'linear-gradient(135deg, #0ea5e9, #a855f7)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 14px', color: '#fff', fontSize: '24px' }}>
                  <i className="fa-solid fa-user-secret"></i>
                </div>
                <h2 style={{ fontSize: '20px', fontWeight: 800, color: '#fff', margin: '0 0 6px 0' }}>Anonymous Sign Up</h2>
                <p style={{ fontSize: '13px', color: '#9ca3af', margin: 0 }}>Generates a random 4-character ID</p>
              </div>

              <form onSubmit={handleSignupSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                <div>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '6px' }}>Campus Email</label>
                  <input 
                    type="email" 
                    required 
                    placeholder="student@university.edu"
                    value={signupEmail}
                    onChange={(e) => setSignupEmail(e.target.value)}
                    style={styles.input}
                  />
                </div>

                <div>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '6px' }}>Password (min 6 characters)</label>
                  <input 
                    type="password" 
                    required 
                    minLength={6}
                    placeholder="••••••••"
                    value={signupPassword}
                    onChange={(e) => setSignupPassword(e.target.value)}
                    style={styles.input}
                  />
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginTop: '4px' }}>
                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '6px' }}>Major</label>
                    <select 
                      value={signupMajor}
                      onChange={(e) => setSignupMajor(e.target.value)}
                      style={styles.select}
                    >
                      <option>Computer Science</option>
                      <option>Engineering</option>
                      <option>Literature</option>
                      <option>Business</option>
                      <option>Architecture</option>
                      <option>Fine Arts</option>
                    </select>
                  </div>
                  <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 600, color: '#9ca3af', marginBottom: '6px' }}>Year</label>
                    <select 
                      value={signupYear}
                      onChange={(e) => setSignupYear(e.target.value)}
                      style={styles.select}
                    >
                      <option>Freshman</option>
                      <option>Sophomore</option>
                      <option>Junior</option>
                      <option>Senior</option>
                      <option>Graduate</option>
                    </select>
                  </div>
                </div>

                <button type="submit" style={{ ...styles.btnPrimary, marginTop: '14px' }}>
                  <span>Generate Identity</span>
                  <i className="fa-solid fa-arrow-right"></i>
                </button>
              </form>

              <div style={{ textAlign: 'center', marginTop: '16px' }}>
                <button 
                  onClick={() => setAuthView('login')}
                  style={{ background: 'none', border: 'none', color: '#38bdf8', fontSize: '12px', cursor: 'pointer', fontWeight: 600 }}
                >
                  Already have an account? Log in
                </button>
              </div>
            </div>
          )}
        </main>

        <footer style={styles.footer}>truetalknow &copy; 2026 • Secure Anonymous Protocol</footer>
      </div>
    );
  }

  return (
    <div style={styles.container}>
      <div style={styles.glow1} className="animate-glow"></div>
      <div style={styles.glow2} className="animate-glow"></div>

      {notification && (
        <div style={styles.toast}>
          <i className="fa-solid fa-bell" style={{ color: '#38bdf8' }}></i>
          <span>{notification}</span>
        </div>
      )}

      {/* Authenticated Header */}
      <header style={styles.header}>
        <div style={styles.headerInner}>
          <div style={styles.logoBox}>
            <div style={styles.uniqueChatLogo}>
              <img src={user.avatarUrl} alt="avatar" style={{ width: '100%', height: '100%', borderRadius: '12px' }} />
              <span style={{ position: 'absolute', bottom: '-2px', right: '-2px', width: '10px', height: '10px', backgroundColor: '#34d399', borderRadius: '50%', border: '2px solid #030712' }}></span>
            </div>
            <div>
              <h1 style={styles.logoTitle}>
                {user.codeName} <span style={styles.badge}>{user.studentCode}</span>
              </h1>
              <p style={{ fontSize: '10px', color: '#9ca3af', margin: 0 }}>{user.major} • {user.year}</p>
            </div>
          </div>

          <div className="desktop-nav" style={styles.nav}>
            <button style={styles.navBtn(activeTab === 'feed')} onClick={() => { setActiveTab('feed'); setInspectingUser(null); }}>
              <i className="fa-solid fa-fire"></i> Feed
            </button>
            <button style={styles.navBtn(activeTab === 'discover')} onClick={() => { setActiveTab('discover'); setInspectingUser(null); }}>
              <i className="fa-solid fa-magnifying-glass"></i> Discover
            </button>
            <button style={styles.navBtn(activeTab === 'reels')} onClick={() => { setActiveTab('reels'); setInspectingUser(null); }}>
              <i className="fa-solid fa-clapperboard"></i> Reels
            </button>
            <button style={styles.navBtn(activeTab === 'messages')} onClick={() => { setActiveTab('messages'); setInspectingUser(null); }}>
              <i className="fa-solid fa-comments"></i> Messages
            </button>
            <button style={styles.navBtn(activeTab === 'profile')} onClick={() => { setActiveTab('profile'); setInspectingUser(null); }}>
              <i className="fa-solid fa-user-secret"></i> Profile
            </button>
          </div>

          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <div className="mobile-only-header-toggle" style={{ display: 'none', alignItems: 'center' }}>
              <button 
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                style={{ background: 'transparent', border: '1px solid rgba(55,65,81,0.8)', color: '#fff', padding: '8px 12px', borderRadius: '10px', cursor: 'pointer', fontSize: '14px' }}
              >
                <i className={`fa-solid ${mobileMenuOpen ? 'fa-xmark' : 'fa-bars'}`}></i>
              </button>
            </div>
            <button 
              onClick={handleLogout}
              style={{ padding: '8px 12px', background: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.3)', color: '#f87171', borderRadius: '12px', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
              title="Log Out"
            >
              <i className="fa-solid fa-power-off"></i>
            </button>
          </div>
        </div>

        {mobileMenuOpen && (
          <div className="mobile-only-header-toggle" style={{ display: 'flex', flexDirection: 'column', backgroundColor: '#030712', borderBottom: '1px solid rgba(55,65,81,0.8)', padding: '16px', gap: '8px' }}>
            <button onClick={() => { setActiveTab('feed'); setInspectingUser(null); setMobileMenuOpen(false); }} style={{ padding: '10px', borderRadius: '10px', border: 'none', background: activeTab === 'feed' ? '#0ea5e9' : 'rgba(17,24,39,0.8)', color: activeTab === 'feed' ? '#030712' : '#fff', textAlign: 'left', fontWeight: 600 }}>
              <i className="fa-solid fa-fire" style={{ width: '24px' }}></i> Feed
            </button>
            <button onClick={() => { setActiveTab('discover'); setInspectingUser(null); setMobileMenuOpen(false); }} style={{ padding: '10px', borderRadius: '10px', border: 'none', background: activeTab === 'discover' ? '#0ea5e9' : 'rgba(17,24,39,0.8)', color: activeTab === 'discover' ? '#030712' : '#fff', textAlign: 'left', fontWeight: 600 }}>
              <i className="fa-solid fa-magnifying-glass" style={{ width: '24px' }}></i> Discover Users
            </button>
            <button onClick={() => { setActiveTab('reels'); setInspectingUser(null); setMobileMenuOpen(false); }} style={{ padding: '10px', borderRadius: '10px', border: 'none', background: activeTab === 'reels' ? '#0ea5e9' : 'rgba(17,24,39,0.8)', color: activeTab === 'reels' ? '#030712' : '#fff', textAlign: 'left', fontWeight: 600 }}>
              <i className="fa-solid fa-clapperboard" style={{ width: '24px' }}></i> Reels
            </button>
            <button onClick={() => { setActiveTab('messages'); setInspectingUser(null); setMobileMenuOpen(false); }} style={{ padding: '10px', borderRadius: '10px', border: 'none', background: activeTab === 'messages' ? '#0ea5e9' : 'rgba(17,24,39,0.8)', color: activeTab === 'messages' ? '#030712' : '#fff', textAlign: 'left', fontWeight: 600 }}>
              <i className="fa-solid fa-comments" style={{ width: '24px' }}></i> Messages
            </button>
            <button onClick={() => { setActiveTab('profile'); setInspectingUser(null); setMobileMenuOpen(false); }} style={{ padding: '10px', borderRadius: '10px', border: 'none', background: activeTab === 'profile' ? '#0ea5e9' : 'rgba(17,24,39,0.8)', color: activeTab === 'profile' ? '#030712' : '#fff', textAlign: 'left', fontWeight: 600 }}>
              <i className="fa-solid fa-user-secret" style={{ width: '24px' }}></i> Profile
            </button>
          </div>
        )}
      </header>

      {/* Main Dashboard Workspace */}
      <main style={styles.main}>
        {inspectingUser ? (
          <div>
            <button 
              onClick={() => setInspectingUser(null)}
              style={{ background: 'none', border: 'none', color: '#38bdf8', cursor: 'pointer', fontSize: '13px', fontWeight: 600, marginBottom: '16px', display: 'flex', alignItems: 'center', gap: '6px' }}
            >
              <i className="fa-solid fa-arrow-left"></i> Back to search
            </button>

            <div style={{ ...styles.card, textAlign: 'center', padding: '28px' }}>
              <img src={inspectingUser.avatarUrl} alt="avatar" style={{ width: '70px', height: '70px', borderRadius: '20px', backgroundColor: '#030712', border: '2px solid rgba(56,189,248,0.4)', margin: '0 auto 12px auto' }} />
              <h2 style={{ fontSize: '18px', fontWeight: 800, margin: '0 0 4px 0' }}>{inspectingUser.codeName}</h2>
              <span style={{ fontSize: '12px', color: '#38bdf8', fontWeight: 600, display: 'block', marginBottom: '14px' }}>{inspectingUser.studentCode}</span>

              <div style={{ display: 'flex', justifyContent: 'center', gap: '16px', fontSize: '12px', color: '#9ca3af', marginBottom: '20px' }}>
                <span>Major: <strong style={{ color: '#fff' }}>{inspectingUser.major}</strong></span>
                <span>Year: <strong style={{ color: '#fff' }}>{inspectingUser.year}</strong></span>
              </div>

              <div style={{ display: 'flex', gap: '10px', justifyContent: 'center' }}>
                {inspectingUser.uid !== user.uid && (
                  <>
                    <button 
                      onClick={() => handleToggleFollow(inspectingUser.uid)}
                      style={{ ...styles.btnPrimary, width: 'auto', padding: '10px 20px', background: (follows[user.uid] || []).includes(inspectingUser.uid) ? 'rgba(55,65,81,0.8)' : undefined }}
                    >
                      <i className={`fa-solid ${(follows[user.uid] || []).includes(inspectingUser.uid) ? 'fa-check' : 'fa-user-plus'}`}></i>
                      <span>{(follows[user.uid] || []).includes(inspectingUser.uid) ? 'Following' : 'Follow Node'}</span>
                    </button>
                    <button 
                      onClick={() => { setActiveChatUser(inspectingUser); setActiveTab('messages'); setInspectingUser(null); }}
                      style={{ ...styles.btnPrimary, width: 'auto', padding: '10px 20px', background: 'linear-gradient(135deg, #6366f1, #a855f7)' }}
                    >
                      <i className="fa-solid fa-message"></i> Message
                    </button>
                  </>
                )}
              </div>
            </div>

            <h3 style={{ fontSize: '15px', fontWeight: 700, margin: '24px 0 12px 0' }}>Whispers by {inspectingUser.codeName}</h3>
            {posts.filter(p => p.authorUid === inspectingUser.uid).length === 0 ? (
              <div style={{ textAlign: 'center', padding: '30px', color: '#6b7280' }}>
                <p>This node hasn't published any whispers yet.</p>
              </div>
            ) : (
              posts
                .filter(p => p.authorUid === inspectingUser.uid)
                .map(post => {
                  const hasLiked = (post.likes || []).includes(user.uid);
                  const postComments = commentsMap[post.id] || [];

                  return (
                    <div key={post.id} style={styles.card}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '12px' }}>
                        <img src={post.authorAvatar} alt="avatar" style={{ width: '36px', height: '36px', borderRadius: '10px' }} />
                        <div>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                            <span style={{ fontWeight: 700, fontSize: '13px', color: '#fff' }}>{post.authorCodeName}</span>
                            <span style={styles.badge}>{post.authorStudentCode}</span>
                          </div>
                          <span style={{ fontSize: '11px', color: '#9ca3af' }}>{new Date(post.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                      </div>

                      {post.content && (
                        <p style={{ fontSize: '14px', lineHeight: '1.5', color: '#f3f4f6', margin: '0 0 12px 0', wordBreak: 'break-word', textAlign: 'left' }}>
                          {post.content}
                        </p>
                      )}

                      {post.mediaUrl && (
                        <div style={{ borderRadius: '16px', overflow: 'hidden', marginBottom: '12px', backgroundColor: '#030712', display: 'flex', justifyContent: 'center' }}>
                          {post.type === 'reel' ? (
                            <video src={post.mediaUrl} controls style={{ width: '100%', maxHeight: '400px', objectFit: 'contain' }} />
                          ) : (
                            <img src={post.mediaUrl} alt="post media" style={{ width: '100%', maxHeight: '350px', objectFit: 'contain' }} />
                          )}
                        </div>
                      )}

                      <div style={{ display: 'flex', gap: '16px', borderTop: '1px solid rgba(55,65,81,0.4)', paddingTop: '12px' }}>
                        <button 
                          onClick={() => handleToggleLike(post)}
                          style={{ background: 'none', border: 'none', color: hasLiked ? '#ec4899' : '#9ca3af', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '13px', cursor: 'pointer', fontWeight: 600 }}
                        >
                          <i className={`${hasLiked ? 'fa-solid' : 'fa-regular'} fa-heart`}></i>
                          <span>{(post.likes || []).length}</span>
                        </button>
                      </div>
                    </div>
                  );
                })
            )}
          </div>
        ) : activeTab === 'discover' ? (
          <div>
            <div style={{ textAlign: 'center', marginBottom: '20px' }}>
              <h2 style={{ fontSize: '18px', fontWeight: 800, margin: '0 0 4px 0' }}>Discover Campus Nodes</h2>
              <p style={{ fontSize: '12px', color: '#9ca3af', margin: 0 }}>Search students by code name, major, or student ID</p>
            </div>

            <div style={{ marginBottom: '20px' }}>
              <input 
                type="text" 
                placeholder="Search by Code Name (e.g. A&33), Major, or ID..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                style={styles.input}
              />
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
              {users
                .filter(u => u.uid !== user.uid && (
                  u.codeName.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  u.major.toLowerCase().includes(searchQuery.toLowerCase()) ||
                  u.studentCode.toLowerCase().includes(searchQuery.toLowerCase())
                ))
                .map(target => {
                  const isFollowing = (follows[user.uid] || []).includes(target.uid);
                  return (
                    <div key={target.uid} style={{ ...styles.card, display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px', marginBottom: '0' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '12px', cursor: 'pointer' }} onClick={() => setInspectingUser(target)}>
                        <img src={target.avatarUrl} alt="avatar" style={{ width: '44px', height: '44px', borderRadius: '14px', border: '1px solid rgba(55,65,81,0.8)' }} />
                        <div>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                            <span style={{ fontWeight: 700, fontSize: '14px', color: '#fff' }}>{target.codeName}</span>
                            <span style={styles.badge}>{target.studentCode}</span>
                          </div>
                          <span style={{ fontSize: '11px', color: '#9ca3af' }}>{target.major} • {target.year}</span>
                        </div>
                      </div>

                      <div style={{ display: 'flex', gap: '8px' }}>
                        <button 
                          onClick={() => handleToggleFollow(target.uid)}
                          style={{ padding: '8px 12px', borderRadius: '12px', border: 'none', background: isFollowing ? 'rgba(55,65,81,0.8)' : '#0ea5e9', color: isFollowing ? '#fff' : '#030712', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
                        >
                          {isFollowing ? 'Following' : 'Follow'}
                        </button>
                        <button 
                          onClick={() => { setActiveChatUser(target); setActiveTab('messages'); }}
                          style={{ padding: '8px 12px', borderRadius: '12px', border: '1px solid rgba(55,65,81,0.8)', background: '#030712', color: '#38bdf8', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
                        >
                          Chat
                        </button>
                      </div>
                    </div>
                  );
                })}
            </div>
          </div>
        ) : activeTab === 'feed' ? (
          <div>
            <div style={styles.card}>
              <form onSubmit={handleCreatePost}>
                <div style={{ display: 'flex', gap: '10px', marginBottom: '12px' }}>
                  <button 
                    type="button" 
                    onClick={() => setNewPostType('text')}
                    style={{ padding: '6px 12px', borderRadius: '10px', border: 'none', background: newPostType === 'text' ? '#0ea5e9' : '#030712', color: newPostType === 'text' ? '#030712' : '#9ca3af', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
                  >
                    <i className="fa-solid fa-pen"></i> Text Whisper
                  </button>
                  <button 
                    type="button" 
                    onClick={() => setNewPostType('image')}
                    style={{ padding: '6px 12px', borderRadius: '10px', border: 'none', background: newPostType === 'image' ? '#0ea5e9' : '#030712', color: newPostType === 'image' ? '#030712' : '#9ca3af', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
                  >
                    <i className="fa-solid fa-image"></i> Image
                  </button>
                  <button 
                    type="button" 
                    onClick={() => setNewPostType('reel')}
                    style={{ padding: '6px 12px', borderRadius: '10px', border: 'none', background: newPostType === 'reel' ? '#0ea5e9' : '#030712', color: newPostType === 'reel' ? '#030712' : '#9ca3af', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
                  >
                    <i className="fa-solid fa-video"></i> Video Reel
                  </button>
                </div>

                <textarea 
                  rows={3}
                  placeholder={`What's on your mind, node ${user.codeName}?`}
                  value={newPostContent}
                  onChange={(e) => setNewPostContent(e.target.value)}
                  style={{ ...styles.input, resize: 'none', marginBottom: '12px', textAlign: 'left' }}
                />

                {newPostType === 'reel' && !mediaPreviewUrl && (
                  <div style={{ marginBottom: '12px', display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' }}>
                    <span style={{ fontSize: '12px', color: '#9ca3af' }}>Select sample or upload up to 50MB:</span>
                    <select 
                      value={customVideoChoice}
                      onChange={(e) => setCustomVideoChoice(e.target.value)}
                      style={{ ...styles.select, width: 'auto', padding: '6px 10px' }}
                    >
                      <option value="sample1">Big Buck Bunny</option>
                      <option value="sample2">Nature Breeze</option>
                      <option value="sample3">Ocean Waves</option>
                    </select>
                  </div>
                )}

                {mediaPreviewUrl && (
                  <div style={{ marginBottom: '12px', position: 'relative', borderRadius: '12px', overflow: 'hidden', maxHeight: '180px', backgroundColor: '#030712', display: 'flex', justifyContent: 'center' }}>
                    {newPostType === 'reel' ? (
                      <video src={mediaPreviewUrl} controls style={{ maxHeight: '180px', width: '100%', objectFit: 'contain' }} />
                    ) : (
                      <img src={mediaPreviewUrl} alt="preview" style={{ maxHeight: '180px', width: '100%', objectFit: 'contain' }} />
                    )}
                    <button 
                      type="button" 
                      onClick={() => { setMediaPreviewUrl(''); setSelectedFile(null); }}
                      style={{ position: 'absolute', top: '8px', right: '8px', backgroundColor: 'rgba(0,0,0,0.7)', color: '#fff', border: 'none', borderRadius: '50%', width: '28px', height: '28px', cursor: 'pointer' }}
                    >
                      <i className="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                )}

                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px' }}>
                  <input 
                    type="file" 
                    ref={fileInputRef} 
                    onChange={handleFileChange} 
                    accept="image/*,video/*" 
                    style={{ display: 'none' }} 
                  />
                  <button 
                    type="button" 
                    onClick={() => fileInputRef.current?.click()}
                    style={{ background: 'transparent', border: '1px dashed rgba(55,65,81,0.8)', color: '#9ca3af', padding: '8px 14px', borderRadius: '12px', fontSize: '12px', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '6px' }}
                  >
                    <i className="fa-solid fa-paperclip"></i> Attach File (Max 50MB)
                  </button>

                  <button type="submit" style={{ ...styles.btnPrimary, width: 'auto', padding: '10px 24px' }}>
                    <span>Publish Whisper</span>
                    <i className="fa-solid fa-paper-plane"></i>
                  </button>
                </div>
              </form>
            </div>

            <div style={{ display: 'flex', gap: '8px', marginBottom: '16px' }}>
              <button 
                onClick={() => setFeedFilter('all')} 
                style={{ padding: '6px 14px', borderRadius: '10px', border: 'none', background: feedFilter === 'all' ? '#38bdf8' : 'rgba(17,24,39,0.8)', color: feedFilter === 'all' ? '#030712' : '#9ca3af', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
              >
                All Whispers
              </button>
              <button 
                onClick={() => setFeedFilter('mine')} 
                style={{ padding: '6px 14px', borderRadius: '10px', border: 'none', background: feedFilter === 'mine' ? '#38bdf8' : 'rgba(17,24,39,0.8)', color: feedFilter === 'mine' ? '#030712' : '#9ca3af', fontSize: '12px', fontWeight: 600, cursor: 'pointer' }}
              >
                My Whispers
              </button>
            </div>

            {posts.filter(p => feedFilter === 'all' || p.authorUid === user.uid).length === 0 ? (
              <div style={{ textAlign: 'center', padding: '40px', color: '#6b7280' }}>
                <i className="fa-solid fa-wind" style={{ fontSize: '32px', marginBottom: '10px' }}></i>
                <p>No whispers found in this frequency.</p>
              </div>
            ) : (
              posts
                .filter(p => feedFilter === 'all' || p.authorUid === user.uid)
                .map(post => {
                  const hasLiked = (post.likes || []).includes(user.uid);
                  const postComments = commentsMap[post.id] || [];
                  const authorObj = users.find(u => u.uid === post.authorUid);

                  return (
                    <div key={post.id} style={styles.card}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '12px' }}>
                        <div 
                          style={{ display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }} 
                          onClick={() => authorObj && setInspectingUser(authorObj)}
                        >
                          <img src={post.authorAvatar} alt="avatar" style={{ width: '40px', height: '40px', borderRadius: '12px', backgroundColor: '#030712', border: '1px solid rgba(55,65,81,0.8)' }} />
                          <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                              <span style={{ fontWeight: 700, fontSize: '14px', color: '#fff' }}>{post.authorCodeName}</span>
                              <span style={styles.badge}>{post.authorStudentCode}</span>
                            </div>
                            <span style={{ fontSize: '11px', color: '#9ca3af' }}>{post.authorMajor} • {new Date(post.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                          </div>
                        </div>

                        {post.authorUid === user.uid && (
                          <button 
                            onClick={() => handleDeletePost(post.id)}
                            style={{ background: 'none', border: 'none', color: '#ef4444', cursor: 'pointer', fontSize: '14px' }}
                            title="Delete whisper"
                          >
                            <i className="fa-solid fa-trash-can"></i>
                          </button>
                        )}
                      </div>

                      {post.content && (
                        <p style={{ fontSize: '14px', lineHeight: '1.5', color: '#f3f4f6', margin: '0 0 12px 0', wordBreak: 'break-word', textAlign: 'left' }}>
                          {post.content}
                        </p>
                      )}

                      {post.mediaUrl && (
                        <div style={{ borderRadius: '16px', overflow: 'hidden', marginBottom: '12px', backgroundColor: '#030712', display: 'flex', justifyContent: 'center' }}>
                          {post.type === 'reel' ? (
                            <video src={post.mediaUrl} controls style={{ width: '100%', maxHeight: '400px', objectFit: 'contain' }} />
                          ) : (
                            <img src={post.mediaUrl} alt="post media" style={{ width: '100%', maxHeight: '350px', objectFit: 'contain' }} />
                          )}
                        </div>
                      )}

                      <div style={{ display: 'flex', gap: '16px', borderTop: '1px solid rgba(55,65,81,0.4)', paddingTop: '12px' }}>
                        <button 
                          onClick={() => handleToggleLike(post)}
                          style={{ background: 'none', border: 'none', color: hasLiked ? '#ec4899' : '#9ca3af', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '13px', cursor: 'pointer', fontWeight: 600 }}
                        >
                          <i className={`${hasLiked ? 'fa-solid' : 'fa-regular'} fa-heart`}></i>
                          <span>{(post.likes || []).length}</span>
                        </button>

                        <button 
                          onClick={() => setVisibleCommentsPostId(visibleCommentsPostId === post.id ? null : post.id)}
                          style={{ background: 'none', border: 'none', color: '#9ca3af', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '13px', cursor: 'pointer', fontWeight: 600 }}
                        >
                          <i className="fa-regular fa-comment"></i>
                          <span>{postComments.length}</span>
                        </button>

                        <button 
                          onClick={() => handleSharePost(post)}
                          style={{ background: 'none', border: 'none', color: '#9ca3af', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '13px', cursor: 'pointer', marginLeft: 'auto' }}
                        >
                          <i className="fa-solid fa-share-nodes"></i>
                        </button>
                      </div>

                      {visibleCommentsPostId === post.id && (
                        <div style={{ marginTop: '14px', borderTop: '1px dashed rgba(55,65,81,0.6)', paddingTop: '12px' }}>
                          <div style={{ maxHeight: '200px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '10px' }}>
                            {postComments.length === 0 ? (
                              <p style={{ fontSize: '12px', color: '#6b7280', textAlign: 'center', margin: '6px 0' }}>No comments yet. Be the first to whisper back!</p>
                            ) : (
                              postComments.map(c => (
                                <div key={c.id} style={{ backgroundColor: '#030712', padding: '8px 12px', borderRadius: '12px', border: '1px solid rgba(55,65,81,0.5)', textAlign: 'left' }}>
                                  <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '2px' }}>
                                    <span style={{ fontSize: '12px', fontWeight: 700, color: '#38bdf8' }}>{c.authorCodeName}</span>
                                    <span style={{ fontSize: '10px', color: '#6b7280' }}>{c.authorStudentCode}</span>
                                  </div>
                                  <p style={{ fontSize: '13px', margin: 0, color: '#e5e7eb', wordBreak: 'break-word', textAlign: 'left' }}>{c.text}</p>
                                </div>
                              ))
                            )}
                          </div>

                          <form onSubmit={(e) => handleAddComment(e, post.id)} style={{ display: 'flex', gap: '8px' }}>
                            <input 
                              type="text" 
                              placeholder="Write an anonymous reply..." 
                              value={newCommentText}
                              onChange={(e) => setNewCommentText(e.target.value)}
                              style={{ ...styles.input, padding: '10px 14px', fontSize: '13px' }}
                            />
                            <button type="submit" style={{ ...styles.btnPrimary, width: 'auto', padding: '10px 16px', fontSize: '13px' }}>
                              Reply
                            </button>
                          </form>
                        </div>
                      )}
                    </div>
                  );
                })
            )}
          </div>
        ) : activeTab === 'reels' && !inspectingUser ? (
          <div>
            <div style={{ textAlign: 'center', marginBottom: '20px' }}>
              <h2 style={{ fontSize: '18px', fontWeight: 800, margin: '0 0 4px 0' }}>Campus Reels</h2>
              <p style={{ fontSize: '12px', color: '#9ca3af', margin: 0 }}>Immersive video snippets from campus nodes</p>
            </div>

            {posts.filter(p => p.type === 'reel').length === 0 ? (
              <div style={{ textAlign: 'center', padding: '40px', color: '#6b7280' }}>
                <i className="fa-solid fa-clapperboard" style={{ fontSize: '32px', marginBottom: '10px' }}></i>
                <p>No video reels shared yet. Upload one from the Feed tab!</p>
              </div>
            ) : (
              posts
                .filter(p => p.type === 'reel')
                .map(reel => {
                  const authorObj = users.find(u => u.uid === reel.authorUid);
                  return (
                    <div key={reel.id} style={{ ...styles.card, maxWidth: '420px', margin: '0 auto 20px auto' }}>
                      <div 
                        style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '12px', cursor: 'pointer' }}
                        onClick={() => authorObj && setInspectingUser(authorObj)}
                      >
                        <img src={reel.authorAvatar} alt="avatar" style={{ width: '36px', height: '36px', borderRadius: '12px', border: '1px solid rgba(55,65,81,0.8)' }} />
                        <div>
                          <span style={{ fontWeight: 700, fontSize: '13px', color: '#fff' }}>{reel.authorCodeName}</span>
                          <span style={{ fontSize: '11px', color: '#9ca3af', display: 'block' }}>{reel.authorMajor}</span>
                        </div>
                      </div>

                      <div style={{ borderRadius: '16px', overflow: 'hidden', backgroundColor: '#000', marginBottom: '12px' }}>
                        <video src={reel.mediaUrl} controls autoPlay loop muted style={{ width: '100%', maxHeight: '450px', display: 'block' }} />
                      </div>

                      {reel.content && <p style={{ fontSize: '13px', margin: '0 0 10px 0', color: '#f3f4f6', textAlign: 'left' }}>{reel.content}</p>}

                      <div style={{ display: 'flex', gap: '16px', fontSize: '13px', color: '#9ca3af' }}>
                        <button onClick={() => handleToggleLike(reel)} style={{ background: 'none', border: 'none', color: 'inherit', display: 'flex', gap: '6px', cursor: 'pointer', fontWeight: 600 }}>
                          <i className="fa-solid fa-heart" style={{ color: '#ec4899' }}></i> {(reel.likes || []).length} Likes
                        </button>
                      </div>
                    </div>
                  );
                })
            )}
          </div>
        ) : activeTab === 'messages' && !inspectingUser ? (
          <div>
            {!activeChatUser ? (
              <div style={styles.card}>
                <h3 style={{ fontSize: '16px', fontWeight: 700, margin: '0 0 14px 0' }}>Direct Student Messages</h3>
                <p style={{ fontSize: '13px', color: '#9ca3af', marginBottom: '16px' }}>Select an active campus node to start an encrypted direct conversation.</p>
                
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  {users.filter(u => u.uid !== user.uid).map(other => (
                    <div 
                      key={other.uid} 
                      onClick={() => setActiveChatUser(other)}
                      style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#030712', padding: '12px 16px', borderRadius: '16px', border: '1px solid rgba(55,65,81,0.6)', cursor: 'pointer' }}
                    >
                      <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <img src={other.avatarUrl} alt="avatar" style={{ width: '36px', height: '36px', borderRadius: '12px' }} />
                        <div>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                            <span style={{ fontWeight: 700, fontSize: '14px', color: '#fff' }}>{other.codeName}</span>
                            <span style={styles.badge}>{other.studentCode}</span>
                          </div>
                          <span style={{ fontSize: '11px', color: '#9ca3af' }}>{other.major}</span>
                        </div>
                      </div>
                      <i className="fa-solid fa-chevron-right" style={{ color: '#9ca3af', fontSize: '12px' }}></i>
                    </div>
                  ))}
                </div>
              </div>
            ) : (
              <div style={styles.card}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px', borderBottom: '1px solid rgba(55,65,81,0.6)', paddingBottom: '12px', marginBottom: '14px' }}>
                  <button onClick={() => setActiveChatUser(null)} style={{ background: 'none', border: 'none', color: '#38bdf8', cursor: 'pointer', fontSize: '14px' }}>
                    <i className="fa-solid fa-arrow-left"></i> Back
                  </button>
                  <img src={activeChatUser.avatarUrl} alt="avatar" style={{ width: '32px', height: '32px', borderRadius: '10px' }} />
                  <div>
                    <span style={{ fontWeight: 700, fontSize: '14px', color: '#fff' }}>{activeChatUser.codeName}</span>
                    <span style={{ fontSize: '10px', color: '#9ca3af', display: 'block' }}>{activeChatUser.major}</span>
                  </div>
                </div>

                <div style={{ height: '300px', overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '10px', marginBottom: '14px', paddingRight: '4px' }}>
                  {messages
                    .filter(m => (m.senderUid === user.uid && m.receiverUid === activeChatUser.uid) || (m.senderUid === activeChatUser.uid && m.receiverUid === user.uid))
                    .sort((a, b) => a.createdAt - b.createdAt)
                    .map(msg => {
                      const isMe = msg.senderUid === user.uid;
                      return (
                        <div key={msg.id} style={{ alignSelf: isMe ? 'flex-end' : 'flex-start', maxWidth: '75%', backgroundColor: isMe ? '#0ea5e9' : '#030712', color: isMe ? '#030712' : '#f9fafb', padding: '10px 14px', borderRadius: '16px', border: isMe ? 'none' : '1px solid rgba(55,65,81,0.6)', textAlign: 'left' }}>
                          <p style={{ fontSize: '13px', margin: 0, wordBreak: 'break-word', fontWeight: isMe ? 600 : 400, textAlign: 'left' }}>{msg.text}</p>
                          <span style={{ fontSize: '9px', opacity: 0.8, display: 'block', textAlign: 'right', marginTop: '2px' }}>{new Date(msg.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                      );
                    })}
                </div>

                <form onSubmit={handleSendMessage} style={{ display: 'flex', gap: '8px' }}>
                  <input 
                    type="text" 
                    placeholder="Type a secure message..."
                    value={newMessageText}
                    onChange={(e) => setNewMessageText(e.target.value)}
                    style={styles.input}
                  />
                  <button type="submit" style={{ ...styles.btnPrimary, width: 'auto', padding: '10px 20px' }}>
                    Send
                  </button>
                </form>
              </div>
            )}
          </div>
        ) : activeTab === 'profile' && !inspectingUser ? (
          <div style={{ ...styles.card, maxWidth: '480px', margin: '0 auto', textAlign: 'center', padding: '32px' }}>
            <img src={user.avatarUrl} alt="avatar" style={{ width: '80px', height: '80px', borderRadius: '24px', backgroundColor: '#030712', border: '2px solid rgba(56,189,248,0.4)', margin: '0 auto 16px auto', boxShadow: '0 10px 25px -5px rgba(56,189,248,0.3)' }} />
            <h2 style={{ fontSize: '20px', fontWeight: 800, margin: '0 0 4px 0' }}>{user.codeName}</h2>
            <p style={{ fontSize: '13px', color: '#38bdf8', fontWeight: 600, margin: '0 0 16px 0' }}>{user.studentCode}</p>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', textAlign: 'left', marginBottom: '20px' }}>
              <div style={{ backgroundColor: '#030712', padding: '12px', borderRadius: '14px', border: '1px solid rgba(55,65,81,0.6)' }}>
                <span style={{ fontSize: '11px', color: '#9ca3af', display: 'block', marginBottom: '2px' }}>Major</span>
                <span style={{ fontSize: '13px', fontWeight: 700, color: '#fff' }}>{user.major}</span>
              </div>
              <div style={{ backgroundColor: '#030712', padding: '12px', borderRadius: '14px', border: '1px solid rgba(55,65,81,0.6)' }}>
                <span style={{ fontSize: '11px', color: '#9ca3af', display: 'block', marginBottom: '2px' }}>Year</span>
                <span style={{ fontSize: '13px', fontWeight: 700, color: '#fff' }}>{user.year}</span>
              </div>
            </div>

            <div style={{ backgroundColor: '#030712', padding: '12px', borderRadius: '14px', border: '1px solid rgba(55,65,81,0.6)', textAlign: 'left', marginBottom: '24px' }}>
              <span style={{ fontSize: '11px', color: '#9ca3af', display: 'block', marginBottom: '2px' }}>Campus Email (Encrypted / Hidden from Peers)</span>
              <span style={{ fontSize: '13px', fontWeight: 600, color: '#e5e7eb' }}>{user.email}</span>
            </div>

            <button onClick={handleLogout} style={{ ...styles.btnPrimary, backgroundColor: 'rgba(239,68,68,0.15)', border: '1px solid rgba(239,68,68,0.4)', color: '#f87171', boxShadow: 'none' }}>
              <i className="fa-solid fa-power-off"></i> Terminate Active Node Session
            </button>
          </div>
        ) : null}
      </main>

      {/* Mobile Bottom Navigation Bar */}
      <nav className="mobile-bottom-nav" style={{ position: 'fixed', bottom: 0, left: 0, right: 0, backgroundColor: 'rgba(3, 7, 18, 0.95)', backdropFilter: 'blur(20px)', borderTop: '1px solid rgba(31, 41, 55, 0.8)', padding: '10px 16px', justifyContent: 'space-around', zIndex: 50 }}>
        <button onClick={() => { setActiveTab('feed'); setInspectingUser(null); }} style={{ background: 'none', border: 'none', color: activeTab === 'feed' ? '#38bdf8' : '#9ca3af', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px', fontSize: '10px', fontWeight: 600 }}>
          <i className="fa-solid fa-fire" style={{ fontSize: '18px' }}></i> Feed
        </button>
        <button onClick={() => { setActiveTab('discover'); setInspectingUser(null); }} style={{ background: 'none', border: 'none', color: activeTab === 'discover' ? '#38bdf8' : '#9ca3af', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px', fontSize: '10px', fontWeight: 600 }}>
          <i className="fa-solid fa-magnifying-glass" style={{ fontSize: '18px' }}></i> Discover
        </button>
        <button onClick={() => { setActiveTab('reels'); setInspectingUser(null); }} style={{ background: 'none', border: 'none', color: activeTab === 'reels' ? '#38bdf8' : '#9ca3af', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px', fontSize: '10px', fontWeight: 600 }}>
          <i className="fa-solid fa-clapperboard" style={{ fontSize: '18px' }}></i> Reels
        </button>
        <button onClick={() => { setActiveTab('messages'); setInspectingUser(null); }} style={{ background: 'none', border: 'none', color: activeTab === 'messages' ? '#38bdf8' : '#9ca3af', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px', fontSize: '10px', fontWeight: 600 }}>
          <i className="fa-solid fa-comments" style={{ fontSize: '18px' }}></i> Messages
        </button>
        <button onClick={() => { setActiveTab('profile'); setInspectingUser(null); }} style={{ background: 'none', border: 'none', color: activeTab === 'profile' ? '#38bdf8' : '#9ca3af', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px', fontSize: '10px', fontWeight: 600 }}>
          <i className="fa-solid fa-user-secret" style={{ fontSize: '18px' }}></i> Profile
        </button>
      </nav>

      <footer style={styles.footer}>truetalknow &copy; 2026 • Secure Anonymous Protocol</footer>
    </div>
  );
}
