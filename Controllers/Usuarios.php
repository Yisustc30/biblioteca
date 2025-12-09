<?php
class Usuarios extends Controller{
    public function __construct() {
        session_start();
        parent::__construct();
    }
    public function index()
    {
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "Usuarios");
        if (!$perm && $id_user != 1) {
            $this->views->getView($this, "permisos");
            exit;
        }
        $this->views->getView($this, "index");
    }
    public function listar()
    {
        if (empty($_SESSION['activo'])) {
            header("location: " . base_url);
        }
        $data = $this->model->getUsuarios();
        for ($i=0; $i < count($data); $i++) { 
            if ($data[$i]['estado'] == 1) {
                if ($data[$i]['id'] != 1) {
                    $data[$i]['estado'] = '<span class="badge badge-success">Activo</span>';
                    $data[$i]['acciones'] = '<div>
                    <button class="btn btn-dark" onclick="btnRolesUser(' . $data[$i]['id'] . ')"><i class="fa fa-key"></i></button>
                    <button class="btn btn-primary" type="button" onclick="btnEditarUser(' . $data[$i]['id'] . ');"><i class="fa fa-pencil-square-o"></i></button>
                    <button class="btn btn-danger" type="button" onclick="btnEliminarUser(' . $data[$i]['id'] . ');"><i class="fa fa-trash-o"></i></button>
                    <div/>';
                }else{
                    $data[$i]['estado'] = '<span class="badge badge-success">Activo</span>';
                    $data[$i]['acciones'] = '<div class"text-center">
                    <span class="badge-primary p-1 rounded">Super Administrador</span>
                    </div>'; 
                }
            }else {
                $data[$i]['estado'] = '<span class="badge badge-danger">Inactivo</span>';
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-success" type="button" onclick="btnReingresarUser(' . $data[$i]['id'] . ');"><i class="fa fa-reply-all"></i></button>
                <div/>';
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function validar()
    {
        $usuario = strClean($_POST['usuario']);
        $clave = strClean($_POST['clave']);
        if (empty($usuario) || empty($clave)) {
            $msg = array('msg' => 'Todo los campos son requeridos', 'icono' => 'warning');
        }else{
            $hash = hash("SHA256", $clave);
            $data = $this->model->getUsuario($usuario, $hash);
            if ($data) {
                $_SESSION['id_usuario'] = $data['id'];
                $_SESSION['usuario'] = $data['usuario'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['activo'] = true;
                $msg = array('msg' => 'Procesando', 'icono' => 'success');
            }else{
                $msg = array('msg' => 'Usuario o contraseña incorrecta', 'icono' => 'warning');
            }
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
{
    $usuario = strClean($_POST['usuario']);
    $nombre = strClean($_POST['nombre']);
    $clave = strClean($_POST['clave']);
    $confirmar = strClean($_POST['confirmar']);
    $id = strClean($_POST['id']);

    if (empty($usuario) || empty($nombre)) {
        $msg = array('msg' => 'Todos los campos son requeridos', 'icono' => 'warning');
        echo json_encode($msg); die();
    }

    // SI ES NUEVO REGISTRO
    if ($id == "") {

        if (empty($clave) || empty($confirmar)) {
            $msg = array('msg' => 'Debe escribir y confirmar la contraseña', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        if ($clave != $confirmar) {
            $msg = array('msg' => 'Las contraseñas no coinciden', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        // VALIDACIONES DE PASSWORD
        if (strlen($clave) < 8) {
            $msg = array('msg' => 'La contraseña debe tener mínimo 8 caracteres', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        if (!preg_match('/[A-Z]/', $clave)) {
            $msg = array('msg' => 'Debe contener al menos UNA letra mayúscula', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        if (!preg_match('/[a-z]/', $clave)) {
            $msg = array('msg' => 'Debe contener al menos UNA letra minúscula', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $clave)) {
            $msg = array('msg' => 'Debe incluir un carácter especial (*, $, %, @, # etc.)', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        // No permitir números consecutivos
        if (preg_match('/012|123|234|345|456|567|678|789/', $clave)) {
            $msg = array('msg' => 'No se permiten números consecutivos (Ej: 123456)', 'icono' => 'warning');
            echo json_encode($msg); die();
        }

        // No permitir letras consecutivas
        $abc = "abcdefghijklmnopqrstuvwxyz";
        for ($i = 0; $i < strlen($abc) - 2; $i++) {
            $seq = substr($abc, $i, 3);
            if (stripos($clave, $seq) !== false) {
                $msg = array('msg' => 'No se permiten letras consecutivas (Ej: abc, bcd)', 'icono' => 'warning');
                echo json_encode($msg); die();
            }
        }

        // HASH SEGURO
        $hash = password_hash($clave, PASSWORD_DEFAULT);

        $data = $this->model->registrarUsuario($usuario, $nombre, $hash);

        if ($data == "ok") {
            $msg = array('msg' => 'Usuario registrado correctamente', 'icono' => 'success');
        } elseif ($data == "existe") {
            $msg = array('msg' => 'El usuario ya existe', 'icono' => 'warning');
        } else {
            $msg = array('msg' => 'Error al registrar usuario', 'icono' => 'error');
        }

    } else {
        // MODIFICANDO
        $data = $this->model->modificarUsuario($usuario, $nombre, $id);
        if ($data == "modificado") {
            $msg = array('msg' => 'Usuario modificado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al modificar', 'icono' => 'error');
        }
    }

    echo json_encode($msg, JSON_UNESCAPED_UNICODE);
    die();
}

    public function editar(int $id)
    {
        $data = $this->model->editarUser($id);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function eliminar(int $id)
    {
        $data = $this->model->accionUser(0, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Usuario dado de baja', 'icono' => 'success');
        }else{
            $msg = array('msg' => 'Error al eliminar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function reingresar(int $id)
    {
        $data = $this->model->accionUser(1, $id);
        if ($data == 1) {
            $msg = array('msg' => 'Usuario restaurado', 'icono' => 'success');
        } else {
            $msg = array('msg' => 'Error al restaurar', 'icono' => 'error');
        }
        echo json_encode($msg, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function permisos($id)
    {
        $id_user = $_SESSION['id_usuario'];
        $perm = $this->model->verificarPermisos($id_user, "roles");
        if (!$perm && $id_user != 1) {
            echo '<div class="card">
                    <div class="card-body text-center">
                        <span class="badge badge-danger">No tienes permisos</span>
                    </div>
                </div>';
            exit;
        }
        $data = $this->model->getPermisos();
        $asignados = $this->model->getDetallePermisos($id);
        $datos = array();
        foreach ($asignados as $asignado) {
            $datos[$asignado['id_permiso']] = true;
        }
        echo '<div class="row">
        <input type="hidden" name="id_usuario" value="' . $id . '">';
        foreach ($data as $row) {
            echo '<div class="d-inline mx-3 text-center">
                    <hr>
                    <label for="" class="font-weight-bold text-capitalize">' . $row['nombre'] . '</label>
                        <div class="center">
                            <input type="checkbox" name="permisos[]" value="' . $row['id'] . '" ';
            if (isset($datos[$row['id']])) {
                echo "checked";
            }
            echo '>
                            <span class="span">On</span>
                            <span class="span">Off</span>
                        </div>
                </div>';
        }
        echo '</div>
        <button class="btn btn-primary mt-3 btn-block" type="button" onclick="registrarPermisos(event);">Actualizar</button>';
        die();
    }
    public function registrarPermisos()
    {
        $id_user = strClean($_POST['id_usuario']);
        $permisos = $_POST['permisos'];
        $this->model->deletePermisos($id_user);
        if ($permisos != "") {
            foreach ($permisos as $permiso) {
                $this->model->actualizarPermisos($id_user, $permiso);
            }
        }
        echo json_encode("ok");
        die();
    }
    public function cambiarPas()
    {
        if ($_POST) {
            $id = $_SESSION['id_usuario'];
            $clave = strClean($_POST['clave_actual']);
            $user = $this->model->editarUser($id);
            if (hash("SHA256", $clave) == $user['clave']) {
                $hash = hash("SHA256", strClean($_POST['clave_nueva']));
                $data = $this->model->actualizarPass($hash, $id);
                if ($data == "modificado") {
                    $msg = array('msg' => 'Contraseña modificado', 'icono' => 'success');
                } else {
                    $msg = array('msg' => 'Error al modificar', 'icono' => 'warning');
                }
            } else {
                $msg = array('msg' => 'Contraseña actual incorrecta', 'icono' => 'warning');
            }
            echo json_encode($msg, JSON_UNESCAPED_UNICODE);
            die();
        }
    }
    public function salir()
    {
        session_destroy();
        header("location: ".base_url);
    }
}