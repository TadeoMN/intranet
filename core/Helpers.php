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
/* ------------ redirección ------------- */
function redirect(string $url) {
    header("Location: $url"); exit;
}
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
    return <<<HTML
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
    HTML;
}
/* ------------ mensajes flash con botones de acción ------------- */
function flash_button(string $type=null, string $title=null, string $text='', string $buttonText=null, string $buttonUrl=null) {
    if($type=== null) {
        if(!isset($_SESSION['flash_button'])) return null;
        $f = $_SESSION['flash_button'];
        unset($_SESSION['flash_button']);
        return $f;
    }
    $_SESSION['flash_button'] = compact('type', 'title', 'text', 'buttonText', 'buttonUrl');
}
/* ------------ imprime si hay mensaje flash con botones de acción ------------- */
function flash_alert_button(): string {
    if(!$f = flash_button()) return '';
    $type = $f['type'] ?? 'info';
    $title = addslashes($f['title'] ?? '');
    $text = addslashes($f['text'] ?? '');
    $buttonText = addslashes($f['buttonText'] ?? 'Aceptar');
    $buttonUrl = htmlspecialchars($f['buttonUrl'] ?? '#', ENT_QUOTES, 'UTF-8');
    return <<<HTML
    <script>
        Swal.fire({
            icon: '$type',
            title: '$title',
            text: '$text',
            showCancelButton: true,
            confirmButtonText: '$buttonText',
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (result.isConfirmed) {
                if ('$buttonUrl' !== '#') {
                    window.location.href = '$buttonUrl';
                }
            }
        });
    </script>
    HTML;
}
/* ------------ mensaje de cierre de sesión ------------- */
function flash_logout(): string {
    return <<<HTML
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
    HTML;
}
/* ------------ script para eliminar empleados ------------- */
function flash_delete_employee(): string {
    return <<<HTML
    <script>
        document.addEventListener('submit', function(e) {
            const formDelete = e.target.closest('.form-delete-employee');
            if (!formDelete) return;

            e.preventDefault();
            const name_employee = formDelete.querySelector('button[type=\"submit\"]').dataset.name || 'Empleado';
            Swal.fire({
                title: 'Eliminar empleado',
                text: `¿Estás seguro de eliminar a \${name_employee}? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                autoHeight: false,
                scrollbarPadding: false
            }).then(result => {
                if (result.isConfirmed) {
                    formDelete.submit();
                }
            });
        });
    </script>
    HTML;
}
/* ------------ script para llenar select de puestos según departamento ------------- */
if(!function_exists('cascadePosition')) {
    function cascadePosition(array $positionMap) : string {
        $json = json_encode($positionMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        return <<<HTML
        <script>
            (function() {
                const positionByDepartment = $json;
                const departmentSelect = document.getElementById('id_department_fk');
                const positionSelect = document.getElementById('id_position_fk');

                function fillPositions(departmentId, preselectedPosition = null) {
                    positionSelect.innerHTML = '<option value="">Seleccione un puesto</option>';
                    if (!departmentId || !positionByDepartment[departmentId]) {
                        positionSelect.disabled = true;
                        return;
                    }
                    positionByDepartment[departmentId].forEach(position => {
                        const option = new Option(position.name_position, position.id_position);
                        positionSelect.add(option);
                    });
                    positionSelect.dataset.preselected = '';
                    positionSelect.disabled = false;

                    if (preselectedPosition) {
                        positionSelect.value = preselectedPosition;
                    }
                }

                departmentSelect.addEventListener('change', e => {
                    const preselectedPosition = positionSelect.dataset.preselected || null;
                    fillPositions(e.target.value, preselectedPosition);
                });

                // Inicializar con el departamento seleccionado
                const preselectedDepartment = departmentSelect.value;
                const preselectedPosition = positionSelect.dataset.preselected || null;
                fillPositions(preselectedDepartment, preselectedPosition);
            })();
        </script>
        HTML;
    }
}
/* ------------ funciones para paginación y ordenamiento ------------- */
function buildUrl(array $changes = []): string {
    $params = [
        'page' => $_GET['page'] ?? 1,
        'limit' => $_GET['limit'] ?? 10,
        'search' => $_GET['search'] ?? '',
        'dateFrom' => $_GET['dateFrom'] ?? '',
        'dateTo' => $_GET['dateTo'] ?? '',
        'sort' => $_GET['sort'] ?? 'code_employee',
        'order' => $_GET['order'] ?? 'asc',
        'status' => $_GET['status'] ?? null
    ];

    $params = array_merge($params, $changes);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return '/employees/list?' . htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
}
function sortLink($colunm, $currentSort, $currentOrder): string {
    $newOrder = ($currentSort === $colunm && $currentOrder === 'asc') ? 'desc' : 'asc';
    return buildUrl(['sort' => $colunm, 'order' => $newOrder, 'page' => 1]);
}
function sortIcon($colunm, $currentSort, $currentOrder): string {
    if ($currentSort !== $colunm) { return 'fa-sort'; }
    return $currentOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
}