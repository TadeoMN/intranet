<header class="bg-dark text-white py-3">    
    <nav class="navbar navbar-dark bg-dark sticky-top">
        <div class="container-fluid justify-content-start">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand mx-3" href="/dashboard">Top Label</a>
            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Top Label</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Mi perfil
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink1">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-user me-3"></i>
                                        Ver perfil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-person-walking-arrow-right me-3"></i>
                                        Solicitar Permiso
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-circle-question me-3"></i>
                                        Ayuda
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item logout-link" href="/logout">
                                        <i class="fa-solid fa-right-from-bracket me-3"></i>
                                        Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Panel de administración
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink2">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-users me-3"></i>
                                        Usuarios
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-shield-halved me-3"></i>
                                        Permisos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-dice-three me-3"></i>
                                        Roles
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-user-clock me-3"></i>
                                        Sesiones Activas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-history me-3"></i>
                                        Historial Sesiones
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a data-mdb-dropdown-init class="nav-link dropdown-toggle" href="#"
                                id="navbarDropdownMenuLink3" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Panel de Recursos Humanos
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink3">
                                  <li><h6 class="dropdown-header">Nuevos Registros</h6></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                        Alta de Empleado
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                        Alta de Perfil de Empleado
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                        Alta de Contrato
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                        Alta de Puesto
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3 tl-icon"></i>
                                        Alta de Incidencia
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><h6 class="dropdown-header">Catálogos</h6></li>
                                <li>
                                    <a class="dropdown-item" href="/employees/list">
                                        <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                        Catálogo de Empleados
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                        Catálogo de Áreas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-list-ul me-3 tl-icon"></i>
                                        Catálogo de Puestos
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><h6 class="dropdown-header">Reportes</h6></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-circle-info me-3 tl-icon"></i>
                                        Reporte de Incidencias
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-circle-info me-3 tl-icon"></i>
                                        Reporte de Calificaciones
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-book-user me-3 tl-icon"></i>
                                        Historial de Puestos
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><h6 class="dropdown-header">Autorizaciones</h6></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-handshake me-3 tl-icon"></i>
                                        Autorizar Permisos/Ausencias
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><h6 class="dropdown-header">Configuración</h6></li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-contract me-3 tl-icon"></i>
                                        Tipos de contratos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-envelope-open-dollar me-3 tl-icon"></i>
                                        Esquemas de pago
                                    </a>
                                </li>
                            </ul>
                        </li>

                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink1" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Panel de Calidad
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdownMenuLink1">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-file-plus me-3"></i>
                                        Alta Incidencia
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-user-graduate me-3"></i>
                                        Calificar
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <i class="fa-solid fa-eye me-3"></i>
                                        Incidencias
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>