<?php

/* ------------ configuración ------------- */
function config(string $path) {
    static $cfg=null;
    if(!$cfg) $cfg = require __DIR__.'/../config/config.php';
    return array_reduce(explode('.',$path), fn($a,$b)=>$a[$b]??null, $cfg);
}

/* ------------ vistas / redirects --------- */
function view(string $path,array $vars=[]) {
    extract($vars, EXTR_SKIP);
    ob_start();
    require __DIR__."/../app/Views/$path.php";
    return ob_get_clean();
}

function redirect(string $url) { header("Location: $url"); exit; }

/* ------------ mensajes flash ------------- */
function flash(string $type=null,string $title=null,string $text='') {
    if($type===null){
        if(!isset($_SESSION['flash'])) return null;
        $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f;
    }
    $_SESSION['flash']=compact('type','title','text');
}

/* ------------ imprime si hay mensaje flash y lo consume ------------- */
function flash_alert(): string {
    if(!$f = flash()) return '';
    [$t,$h,$m] = array_map(fn($v)=>addslashes($v), [$f['type'],$f['title'],$f['text']]);
    return 
    "
    <script>
        Swal.fire({
            icon:'$t',
            title:'$h',
            text:'$m',
            // timer: 8000,
            toast: false,
            heightAuto: false,
            scrollbarPadding: false,
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false
        });
    </script>
    ";
}

function flash_logout(): string {
    return
    "
    <script>
        function confirmLogout (e) {
            if (e) e.preventDefault();
            Swal.fire({
                title: 'Cerrar sesión',
                text:  '¿Quieres cerrar sesión?',
                icon:  'question',
                showCancelButton: true,
                confirmButtonText: 'Salir',
                cancelButtonText: 'Cancelar'
            }).then(r => {
                if (r.isConfirmed) window.location.href = '/logout';
            });
        }
            
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.logout-link').forEach(a =>
                a.addEventListener('click', confirmLogout)
            );
        });
    </script>
    ";
}

function js_session_tables(): string {
    return
    "
        $('#tblActivas, #tblHist').DataTable({
            order: [[3, 'desc']],
            language:{ url:'/assets/vendor/datatables/i18n/es-ES.json' }
        });

    ";
}

function js_session_close(): string {
    return
    "
        function closeSession(id_session) {
            $.ajax({
                url:'/sessions/close',
                type:'POST',
                data:{id_session},
                dataType:'json'
            }).done(res=>{
                console.log('json ok', res);

                if(res.ok) {
                    Swal.fire('Éxito', 'Sesión cerrada correctamente', 'success');
                    setTimeout(() => location.reload(), 3000);
                } else {
                    Swal.fire('Ups', res.msg || 'No se pudo cerrar', 'error');
                }

            }).fail((jq,txt,err)=>{
                console.warn('ajax fail', jq.status, jq.responseText);
                Swal.fire('Error', 'Petición fallida: '+jq.status, 'error');
            });
        }

        $(document).on('click','.close-sess',e=>{
            e.preventDefault();
            const id_session = $(e.currentTarget).data('id_session');
            Swal.fire({
                title:'Cerrar sesión',
                text:'¿Deseas cerrarla?',
                icon:'warning',
                showCancelButton:true,
                confirmButtonText:'Cerrar',
                cancelButtonText:'Cancelar'
            }).then(r=>{
                if(r.isConfirmed) closeSession(id_session);
            });
        });";
}

function js_employee_tables(): string {
    return
    "
        $('#tblEmployees').DataTable({
            language:{ url:'/assets/vendor/datatables/i18n/es-ES.json' }
        });
    ";
}

function buildUrl(array $changes = []): string {
    $params = [
        'page' => $_GET['page'] ?? 1,
        'limit' => $_GET['limit'] ?? 10,
        'search' => $_GET['search'] ?? '',
        'dateFrom' => $_GET['dateFrom'] ?? '',
        'dateTo' => $_GET['dateTo'] ?? '',
        'sort' => $_GET['sort'] ?? 'code_employee',
        'order' => $_GET['order'] ?? 'asc'
    ];

    $params = array_merge($params, $changes);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/employees/list?' . htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
}

function pageUrl(int $page, array $params = []): string {
    $params = array_merge([
        'page' => $page,
        'limit' => $_GET['limit'] ?? 10,
        'search' => $_GET['search'] ?? '',
        'dateFrom' => $_GET['dateFrom'] ?? '',
        'dateTo' => $_GET['dateTo'] ?? ''
    ], $params);
    return '/employees/list?' . http_build_query($params);
}