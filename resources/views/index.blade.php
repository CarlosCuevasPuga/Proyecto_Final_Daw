<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PAPM – Passport Pal Motril | Rutas Gastronómicas</title>
  <meta name="description" content="Descubre los mejores sabores de Motril. Sigue rutas gastronómicas, gana puntos y consigue descuentos exclusivos." />
  <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="navbar" role="navigation" aria-label="Navegación principal">
  <div class="container">
    <a href="./index.blade.php" class="navbar-brand">🍊 PAPM <span>Motril</span></a>
    <div class="navbar-links">
      <a href="./index.blade.php" class="active">Inicio</a>
      <a href="./rutas.blade.php">Rutas</a>
      <a href="./restaurantes.blade.php">Restaurantes</a>
      <a href="./cupones.blade.php">Cupones</a>
      <span id="nav-auth">
        <a href="./login.blade.php"    class="btn btn-outline btn-sm">Entrar</a>
        <a href="./registro.blade.php" class="btn btn-primary btn-sm">Registrarse</a>
      </span>
      <span id="nav-user" class="hidden flex gap-1" style="align-items:center">
        <span class="puntos-badge">⭐ <span id="nav-puntos">0</span> pts</span>
        <a href="./perfil.blade.php" class="btn btn-ghost btn-sm">👤 <span id="nav-username"></span></a>
        <button onclick="Auth.logout()" class="btn btn-ghost btn-sm">Salir</button>
      </span>
    </div>
    <button class="navbar-menu-btn" aria-label="Abrir menú">☰</button>
  </div>
</nav>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero" aria-label="Portada">
  <div class="hero-bg" role="img" aria-label="Vista del mediterráneo"></div>
  <div class="container">
    <div class="hero-content">
      <div class="badge badge-naranja mb-2">🌊 Costa Tropical, Granada</div>
      <h1>Explora Motril<br/>a través de su <span>gastronomía</span></h1>
      <p>Sigue nuestras rutas curadas, descubre restaurantes locales, gana puntos en cada visita y canjéalos por descuentos exclusivos.</p>
      <div class="hero-btns">
        <a href="/rutas.html"    class="btn btn-primary btn-lg">🗺️ Ver rutas gastronómicas</a>
        <a href="/registro.html" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff;">Crear cuenta gratis</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><span>8+</span><p>Restaurantes</p></div>
        <div class="hero-stat"><span>4</span><p>Rutas activas</p></div>
        <div class="hero-stat"><span>500+</span><p>Puntos ganables</p></div>
        <div class="hero-stat"><span>5</span><p>Cupones disponibles</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ CÓMO FUNCIONA ═══════════════ -->
<section class="seccion" style="background:var(--gris-suave);">
  <div class="container text-center">
    <div class="badge badge-azul mb-2">¿Cómo funciona?</div>
    <h2 class="mb-3">Tu pasaporte gastronómico<br/>en tres pasos</h2>
    <div class="grid-3 mt-4">
      <div class="feature-card">
        <div class="feature-icon" style="background:#dbeafe;">🗺️</div>
        <h3>1. Elige una ruta</h3>
        <p>Selecciona entre nuestras rutas curadas: del puerto, del centro histórico, gourmet o dulce.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#fed7aa;">📍</div>
        <h3>2. Visita los locales</h3>
        <p>Sigue el mapa interactivo y visita cada restaurante de la ruta marcando las paradas completadas.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:#dcfce7;">🏆</div>
        <h3>3. Gana puntos y premios</h3>
        <p>Acumula puntos con cada ruta completada y canjéalos por cupones de descuento exclusivos.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ RUTAS DESTACADAS ═══════════════ -->
<section class="seccion">
  <div class="container">
    <div class="flex gap-2 mb-3" style="justify-content:space-between;flex-wrap:wrap;">
      <div><div class="badge badge-naranja mb-1">Rutas</div><h2>Rutas gastronómicas</h2></div>
      <a href="/rutas.html" class="btn btn-outline">Ver todas →</a>
    </div>
    <div class="grid-3" id="rutas-destacadas"></div>
  </div>
</section>

<!-- ═══════════════ RESTAURANTES DESTACADOS ═══════════════ -->
<section class="seccion" style="background:var(--arena);">
  <div class="container">
    <div class="flex gap-2 mb-3" style="justify-content:space-between;flex-wrap:wrap;">
      <div><div class="badge badge-verde mb-1">Restaurantes</div><h2>Los más valorados</h2></div>
      <a href="/restaurantes.html" class="btn btn-outline">Ver todos →</a>
    </div>
    <div class="grid-3" id="restaurantes-destacados"></div>
  </div>
</section>

<!-- ═══════════════ PREMIUM ═══════════════ -->
<section class="seccion text-center" style="background:linear-gradient(135deg,#1a5f7a,#0d3d52);color:#fff;">
  <div class="container">
    <div class="badge badge-dorado mb-2">✨ Premium</div>
    <h2 style="color:#fff;">Desbloquea todo con Premium</h2>
    <p style="color:rgba(255,255,255,.75);max-width:500px;margin:1rem auto 2rem;">
      Accede a rutas exclusivas, descuentos superiores y 500 puntos de bienvenida. Desde sólo 4,99 €/mes.
    </p>
    <div class="grid-2" style="max-width:700px;margin:0 auto;">
      <div class="card" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);">
        <div class="card-body">
          <h3 style="color:#fff;">Mensual</h3>
          <div style="font-size:2.5rem;font-weight:800;color:var(--naranja);margin:.5rem 0;">4,99€</div>
          <p style="color:rgba(255,255,255,.6);">por mes</p>
          <ul style="list-style:none;text-align:left;margin:1rem 0;color:rgba(255,255,255,.8);">
            <li>✅ Rutas exclusivas</li><li>✅ Cupones premium</li><li>✅ +100 pts bienvenida</li>
          </ul>
          <a href="/registro.html?plan=premium_mensual" class="btn btn-primary" style="width:100%;justify-content:center;">Empezar</a>
        </div>
      </div>
      <div class="card" style="background:rgba(255,255,255,.12);border:2px solid var(--dorado);">
        <div class="card-body">
          <div class="badge badge-dorado mb-1">Más popular</div>
          <h3 style="color:#fff;">Anual</h3>
          <div style="font-size:2.5rem;font-weight:800;color:var(--dorado);margin:.5rem 0;">39,99€</div>
          <p style="color:rgba(255,255,255,.6);">por año <span style="color:var(--dorado)">· Ahorra 20€</span></p>
          <ul style="list-style:none;text-align:left;margin:1rem 0;color:rgba(255,255,255,.8);">
            <li>✅ Todo lo del mensual</li><li>✅ +500 pts bienvenida</li><li>✅ Acceso anticipado</li>
          </ul>
          <a href="/registro.html?plan=premium_anual" class="btn btn-lg" style="width:100%;justify-content:center;background:var(--dorado);color:#fff;border:none;">Empezar ahora</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ FOOTER ═══════════════ -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="navbar-brand mb-2" style="font-size:1.3rem;">🍊 PAPM <span style="color:var(--naranja);">Motril</span></div>
        <p style="font-size:.85rem;">Passport Pal Motril — Tu guía gastronómica de la Costa Tropical</p>
      </div>
      <div>
        <h4>Explorar</h4>
        <nav style="display:flex;flex-direction:column;gap:.5rem;">
          <a href="/rutas.html">Rutas gastronómicas</a>
          <a href="/restaurantes.html">Restaurantes</a>
          <a href="/cupones.html">Cupones y descuentos</a>
        </nav>
      </div>
      <div>
        <h4>Cuenta</h4>
        <nav style="display:flex;flex-direction:column;gap:.5rem;">
          <a href="/login.html">Iniciar sesión</a>
          <a href="/registro.html">Registrarse gratis</a>
          <a href="/perfil.html">Mi perfil</a>
        </nav>
      </div>
      <div>
        <h4>Contacto</h4>
        <p style="font-size:.85rem;">info@papmotril.com</p>
        <p style="font-size:.85rem;">Motril, Granada · España</p>
      </div>
    </div>
    <div class="footer-bottom">© 2025 PAPM – Passport Pal Motril · Laravel 11 + Sanctum</div>
  </div>
</footer>

<script src="/js/api.js"></script>
<script>
async function cargarRutas() {
    const el = document.getElementById('rutas-destacadas');
    showLoader(el);
    try {
        const data  = await api.get('/rutas');
        const rutas = data.rutas.slice(0, 3);
        const difColor = { facil:'badge-verde', media:'badge-dorado', dificil:'badge-naranja' };
        el.innerHTML = rutas.map(r => `
            <article class="card">
                <img class="card-img" src="${r.imagen_url || ''}" alt="${r.nombre}" loading="lazy"/>
                <div class="card-body">
                    <div class="card-meta">
                        <span class="badge ${difColor[r.dificultad]}">${r.dificultad}</span>
                        <span class="badge badge-azul">⏱ ${r.duracion_min} min</span>
                        <span class="badge badge-azul">🏁 ${r.num_paradas ?? ''} paradas</span>
                    </div>
                    <h3 class="card-title">${r.nombre}</h3>
                    <p style="font-size:.875rem;">${(r.descripcion || '').slice(0,100)}…</p>
                </div>
                <div class="card-footer">
                    <span class="puntos-badge">+${r.puntos_reward} puntos</span>
                    <a href="/rutas.html" class="btn btn-primary btn-sm">Ver ruta</a>
                </div>
            </article>`).join('');
    } catch(e) { el.innerHTML = '<p class="text-muted">No se pudieron cargar las rutas.</p>'; }
}

async function cargarRestaurantes() {
    const el = document.getElementById('restaurantes-destacados');
    showLoader(el);
    try {
        const data  = await api.get('/restaurantes?orden=valoracion');
        const rests = data.restaurantes.slice(0, 3);
        el.innerHTML = rests.map(r => `
            <article class="card">
                <img class="card-img" src="${r.imagen_url || ''}" alt="${r.nombre}" loading="lazy"/>
                <div class="card-body">
                    <div class="card-meta">
                        <span class="badge badge-naranja">${r.categoria.replace('_',' ')}</span>
                        <span class="badge badge-dorado">${r.precio_medio}</span>
                    </div>
                    <h3 class="card-title">${r.nombre}</h3>
                    <div class="flex gap-1 mt-1">
                        ${renderStars(r.valoracion)}
                        <small class="text-muted">(${r.num_valoraciones})</small>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">📍 ${r.direccion.split(',')[0]}</small>
                    <a href="/restaurantes.html" class="btn btn-outline btn-sm">Ver más</a>
                </div>
            </article>`).join('');
    } catch(e) { el.innerHTML = '<p class="text-muted">No se pudieron cargar los restaurantes.</p>'; }
}

cargarRutas();
cargarRestaurantes();
</script>
</body>
</html>
