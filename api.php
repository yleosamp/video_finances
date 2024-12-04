<?php
header('Content-Type: application/json');
session_start();

$conn = new mysqli('localhost', 'root', '', 'videofinances');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro de conexão']));
}

$action = $_POST['action'] ?? '';

if (!isset($_SESSION['user_id']) && !in_array($action, ['login', 'register', 'check_auth'])) {
    die(json_encode(['success' => false, 'message' => 'Usuário não autenticado']));
}

if ($_POST['action'] === 'delete_video') {
    if (!isset($_POST['video_id'])) {
        die(json_encode(['success' => false, 'message' => 'ID do vídeo não fornecido']));
    }
    
    $video_id = intval($_POST['video_id']);
    
    // Verificar se o vídeo pertence ao usuário
    $stmt = $conn->prepare("SELECT user_id FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $video = $result->fetch_assoc();
    
    if (!$video || $video['user_id'] != $_SESSION['user_id']) {
        die(json_encode(['success' => false, 'message' => 'Vídeo não encontrado']));
    }
    
    // Excluir o vídeo
    $stmt = $conn->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $video_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o vídeo']);
    }
    exit;
}

switch ($action) {
    case 'login':
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        
        $result = $conn->query("SELECT id, password FROM users WHERE email = '$email'");
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
        }
        break;

    case 'register':
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
        if ($conn->query($sql)) {
            $_SESSION['user_id'] = $conn->insert_id;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar']);
        }
        break;

    case 'check_auth':
        if (isset($_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'add_video':
        $user_id = $_SESSION['user_id'];
        $name = $conn->real_escape_string($_POST['name']);
        $price = floatval($_POST['price']);
        $currency = $_POST['currency'];
        $month = intval($_POST['month']);
        $year = intval($_POST['year']) ?: date('Y');
        $people_count = intval($_POST['people_count']) ?: 1;
        
        $sql = "INSERT INTO videos (user_id, name, price, currency, month, year, people_count) 
                VALUES ($user_id, '$name', $price, '$currency', $month, $year, $people_count)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar vídeo']);
        }
        break;

    case 'get_videos':
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        $user_id = $_SESSION['user_id'];
        
        $sql = "SELECT * FROM videos WHERE user_id = $user_id AND month = $month AND year = $year ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        $videos = [];
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
        
        echo json_encode(['success' => true, 'videos' => $videos]);
        break;

    case 'toggle_payment':
        $video_id = intval($_POST['video_id']);
        $user_id = $_SESSION['user_id'];
        
        $sql = "UPDATE videos SET is_paid = NOT is_paid 
                WHERE id = $video_id AND user_id = $user_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
        break;

    case 'get_notes':
        $video_id = intval($_POST['video_id']);
        $user_id = $_SESSION['user_id'];
        
        $sql = "SELECT notes FROM videos WHERE id = $video_id AND user_id = $user_id";
        $result = $conn->query($sql);
        $video = $result->fetch_assoc();
        
        echo json_encode(['success' => true, 'notes' => $video['notes']]);
        break;

    case 'save_notes':
        $video_id = intval($_POST['video_id']);
        $user_id = $_SESSION['user_id'];
        $notes = $conn->real_escape_string($_POST['notes']);
        
        $sql = "UPDATE videos SET notes = '$notes' 
                WHERE id = $video_id AND user_id = $user_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar notas']);
        }
        break;

    case 'update_currency':
        $video_id = intval($_POST['video_id']);
        $user_id = $_SESSION['user_id'];
        $currency = $_POST['currency'];
        $price = floatval($_POST['price']);
        
        $sql = "UPDATE videos SET currency = '$currency', price = $price 
                WHERE id = $video_id AND user_id = $user_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar moeda']);
        }
        break;
}

$conn->close();
