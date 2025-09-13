<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Posyandu - API Documentation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #334155;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .header h1 {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .api-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .api-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }

        .api-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .icon-chat { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .icon-baby { background: linear-gradient(135deg, #fa709a, #fee140); }
        .icon-info { background: linear-gradient(135deg, #a8edea, #fed6e3); }
        .icon-schedule { background: linear-gradient(135deg, #ffecd2, #fcb69f); }
        .icon-medical { background: linear-gradient(135deg, #89f7fe, #66a6ff); }
        .icon-vitamin { background: linear-gradient(135deg, #fdbb2d, #22c1c3); }
        .icon-death { background: linear-gradient(135deg, #667eea, #764ba2); }
        .icon-consult { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .icon-parent { background: linear-gradient(135deg, #4ecdc4, #44a08d); }
        .icon-exam { background: linear-gradient(135deg, #ffecd2, #fcb69f); }
        .icon-ref { background: linear-gradient(135deg, #667eea, #764ba2); }
        .icon-user { background: linear-gradient(135deg, #ff9a9e, #fecfef); }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .endpoint-list {
            list-style: none;
        }

        .endpoint-item {
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            transition: all 0.2s ease;
        }

        .endpoint-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }

        .endpoint-method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 0.5rem;
        }

        .method-get {
            background: #dcfce7;
            color: #166534;
        }

        .method-post {
            background: #fef3c7;
            color: #92400e;
        }

        .method-put {
            background: #e0e7ff;
            color: #3730a3;
        }

        .method-delete {
            background: #fecaca;
            color: #991b1b;
        }

        .endpoint-path {
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            color: #374151;
        }

        .endpoint-description {
            font-size: 0.85rem;
            color: #6b7280;
            margin-left: 0.5rem;
        }

        .footer {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            color: white;
            margin-top: 2rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .footer-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .search-box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            color: #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .api-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .api-section {
                padding: 1.5rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heartbeat"></i> SIM Posyandu</h1>
            <p>Sistem Informasi Manajemen Posyandu - Dokumentasi API yang lengkap dan mudah digunakan</p>
        </div>

        <div class="search-box">
            <input 
                type="text" 
                class="search-input" 
                placeholder="🔍 Cari endpoint API..." 
                id="searchInput"
                oninput="filterEndpoints()"
            >
        </div>

        <div class="api-grid" id="apiGrid">
            <!-- Chat Section -->
            <div class="api-section" data-category="chat">
                <div class="section-header">
                    <div class="section-icon icon-chat">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="section-title">Chat</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/chat</span>
                        <span class="endpoint-description">→ List semua chat</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/chat/{id}</span>
                        <span class="endpoint-description">→ Detail chat</span>
                    </li>
                </ul>
            </div>

            <!-- Balita Section -->
            <div class="api-section" data-category="balita">
                <div class="section-header">
                    <div class="section-icon icon-baby">
                        <i class="fas fa-baby"></i>
                    </div>
                    <h3 class="section-title">Balita</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/balita</span>
                        <span class="endpoint-description">→ List balita</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/balita/{id}</span>
                        <span class="endpoint-description">→ Detail balita</span>
                    </li>
                </ul>
            </div>

            <!-- Informasi Section -->
            <div class="api-section" data-category="informasi">
                <div class="section-header">
                    <div class="section-icon icon-info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="section-title">Informasi</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/informasi</span>
                        <span class="endpoint-description">→ List informasi</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/informasi/{id}</span>
                        <span class="endpoint-description">→ Detail informasi</span>
                    </li>
                </ul>
            </div>

            <!-- Jadwal Section -->
            <div class="api-section" data-category="jadwal">
                <div class="section-header">
                    <div class="section-icon icon-schedule">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="section-title">Jadwal</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/jadwal</span>
                        <span class="endpoint-description">→ List jadwal</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/jadwal/{id}</span>
                        <span class="endpoint-description">→ Detail jadwal</span>
                    </li>
                </ul>
            </div>

            <!-- Imunisasi Section -->
            <div class="api-section" data-category="imunisasi">
                <div class="section-header">
                    <div class="section-icon icon-medical">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h3 class="section-title">Imunisasi</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/imunisasi</span>
                        <span class="endpoint-description">→ List imunisasi</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/imunisasi/{id}</span>
                        <span class="endpoint-description">→ Detail imunisasi</span>
                    </li>
                </ul>
            </div>

            <!-- Vitamin Section -->
            <div class="api-section" data-category="vitamin">
                <div class="section-header">
                    <div class="section-icon icon-vitamin">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3 class="section-title">Vitamin</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/vitamin</span>
                        <span class="endpoint-description">→ List vitamin</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/vitamin/{id}</span>
                        <span class="endpoint-description">→ Detail vitamin</span>
                    </li>
                </ul>
            </div>

            <!-- Kematian Section -->
            <div class="api-section" data-category="kematian">
                <div class="section-header">
                    <div class="section-icon icon-death">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="section-title">Data Kematian</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/kematian</span>
                        <span class="endpoint-description">→ List data kematian</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/kematian/{id}</span>
                        <span class="endpoint-description">→ Detail data kematian</span>
                    </li>
                </ul>
            </div>

            <!-- Konsultasi Section -->
            <div class="api-section" data-category="konsultasi">
                <div class="section-header">
                    <div class="section-icon icon-consult">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3 class="section-title">Konsultasi</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/konsultasi</span>
                        <span class="endpoint-description">→ List konsultasi</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/konsultasi/{id}</span>
                        <span class="endpoint-description">→ Detail konsultasi</span>
                    </li>
                </ul>
            </div>

            <!-- Orang Tua Section -->
            <div class="api-section" data-category="orangtua">
                <div class="section-header">
                    <div class="section-icon icon-parent">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="section-title">Orang Tua</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/orangtua</span>
                        <span class="endpoint-description">→ List orang tua</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/orangtua/{id}</span>
                        <span class="endpoint-description">→ Detail orang tua</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ortu-bayi</span>
                        <span class="endpoint-description">→ List ortu bayi</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ortu-bayi/{id}</span>
                        <span class="endpoint-description">→ Detail ortu bayi</span>
                    </li>
                </ul>
            </div>

            <!-- Pemeriksaan Section -->
            <div class="api-section" data-category="pemeriksaan">
                <div class="section-header">
                    <div class="section-icon icon-exam">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3 class="section-title">Pemeriksaan</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/pemeriksaan</span>
                        <span class="endpoint-description">→ List pemeriksaan</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/pemeriksaan/{id}</span>
                        <span class="endpoint-description">→ Detail pemeriksaan</span>
                    </li>
                </ul>
            </div>

            <!-- Referensi Pertumbuhan Section -->
            <div class="api-section" data-category="referensi">
                <div class="section-header">
                    <div class="section-icon icon-ref">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="section-title">Referensi Pertumbuhan</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-bb-u-laki</span>
                        <span class="endpoint-description">→ BB/U Laki</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-bb-u-perempuan</span>
                        <span class="endpoint-description">→ BB/U Perempuan</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-pb-u-laki</span>
                        <span class="endpoint-description">→ PB/U Laki</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-pb-u-perempuan</span>
                        <span class="endpoint-description">→ PB/U Perempuan</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-standar-bb-pb-laki</span>
                        <span class="endpoint-description">→ Standar BB/PB Laki</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/ref-standar-bb-pb-perempuan</span>
                        <span class="endpoint-description">→ Standar BB/PB Perempuan</span>
                    </li>
                </ul>
            </div>

            <!-- User Section -->
            <div class="api-section" data-category="user">
                <div class="section-header">
                    <div class="section-icon icon-user">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="section-title">User Management</h3>
                </div>
                <ul class="endpoint-list">
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/user</span>
                        <span class="endpoint-description">→ List user</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-get">GET</span>
                        <span class="endpoint-path">/api/user/{id}</span>
                        <span class="endpoint-description">→ Detail user</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-post">POST</span>
                        <span class="endpoint-path">/api/user</span>
                        <span class="endpoint-description">→ Tambah user</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-put">PUT</span>
                        <span class="endpoint-path">/api/user/{id}</span>
                        <span class="endpoint-description">→ Update user</span>
                    </li>
                    <li class="endpoint-item">
                        <span class="endpoint-method method-delete">DELETE</span>
                        <span class="endpoint-path">/api/user/{id}</span>
                        <span class="endpoint-description">→ Hapus user</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <div class="footer-info">
                    <span class="footer-badge">
                        <i class="fas fa-code"></i> CodeIgniter API
                    </span>
                    <span class="footer-badge">
                        <i class="fas fa-heartbeat"></i> Posyandu System
                    </span>
                </div>
                <div class="footer-info">
                    <span class="footer-badge">
                        <i class="fas fa-clock"></i> Last Updated: 2025
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterEndpoints() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sections = document.querySelectorAll('.api-section');
            
            sections.forEach(section => {
                const sectionTitle = section.querySelector('.section-title').textContent.toLowerCase();
                const endpoints = section.querySelectorAll('.endpoint-path');
                let hasMatch = false;
                
                // Check if section title matches
                if (sectionTitle.includes(searchTerm)) {
                    hasMatch = true;
                }
                
                // Check if any endpoint matches
                endpoints.forEach(endpoint => {
                    if (endpoint.textContent.toLowerCase().includes(searchTerm)) {
                        hasMatch = true;
                    }
                });
                
                // Show/hide section based on match
                if (hasMatch || searchTerm === '') {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            });
        }

        // Add smooth scroll behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation
            const sections = document.querySelectorAll('.api-section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    section.style.transition = 'all 0.5s ease';
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>