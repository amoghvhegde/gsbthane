<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Api\ApiClient;
use App\Config\ApiConfig;
use App\Controllers\ApiController;
use App\Controllers\MemberApiController;
use App\Models\ApiMember;

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controllers
$apiController = new ApiController();
$memberController = new MemberApiController();

// Process form submissions
$message = '';
$messageType = '';

// Handle login form
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($apiController->login($username, $password)) {
        $message = 'Login successful!';
        $messageType = 'success';
    } else {
        $message = 'Login failed: ' . implode(', ', $apiController->getErrors());
        $messageType = 'error';
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $apiController->logout();
    $message = 'You have been logged out.';
    $messageType = 'success';
}

// Handle create member form
if (isset($_POST['create_member']) && $apiController->isAuthenticated()) {
    $memberData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'membership_level' => $_POST['membership_level'] ?? 'Standard',
        'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
        'active' => isset($_POST['active']) ? true : false,
    ];
    
    $newMember = $memberController->createMember($memberData);
    
    if ($newMember) {
        $message = 'Member created successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to create member: ' . implode(', ', $memberController->getErrors());
        $messageType = 'error';
    }
}

// Handle update member form
if (isset($_POST['update_member']) && $apiController->isAuthenticated()) {
    $memberId = $_POST['member_id'] ?? '';
    $memberData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'membership_level' => $_POST['membership_level'] ?? 'Standard',
        'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
        'active' => isset($_POST['active']) ? true : false,
    ];
    
    if ($memberController->updateMember($memberId, $memberData)) {
        $message = 'Member updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to update member: ' . implode(', ', $memberController->getErrors());
        $messageType = 'error';
    }
}

// Handle delete member
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && $apiController->isAuthenticated()) {
    $memberId = $_GET['id'];
    
    if ($memberController->deleteMember($memberId)) {
        $message = 'Member deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete member: ' . implode(', ', $memberController->getErrors());
        $messageType = 'error';
    }
}

// Get member to edit if specified
$editMember = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMember = $memberController->getMemberById($_GET['id']);
}

// Fetch members if authenticated
$members = [];
if ($apiController->isAuthenticated()) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $members = $memberController->getAllMembers($page, 10);
    
    // Handle search if present
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $members = $memberController->searchMembers($_GET['search']);
    }
}

// Get user data if authenticated
$userData = $apiController->isAuthenticated() ? $apiController->getUserData() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Integration Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .auth-section {
            text-align: right;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-form, .member-form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .members-list {
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], 
        input[type="email"], 
        input[type="password"], 
        input[type="tel"], 
        input[type="date"],
        select, 
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        textarea {
            height: 80px;
        }
        
        button, .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        button:hover, .button:hover {
            background-color: #45a049;
        }
        
        .button-secondary {
            background-color: #6c757d;
        }
        
        .button-secondary:hover {
            background-color: #5a6268;
        }
        
        .button-danger {
            background-color: #dc3545;
        }
        
        .button-danger:hover {
            background-color: #c82333;
        }
        
        .button-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .search-form {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-form input {
            flex: 1;
            margin-right: 10px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            margin-right: 5px;
            border: 1px solid transparent;
            border-bottom: none;
        }
        
        .tab.active {
            border-color: #ddd;
            border-bottom-color: white;
            margin-bottom: -1px;
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>FastAPI + PHP Integration Example</h1>
            <div class="auth-section">
                <?php if ($apiController->isAuthenticated()): ?>
                    <div>
                        Welcome, <?php echo htmlspecialchars($userData['email'] ?? 'User'); ?>
                        <a href="?action=logout" class="button button-secondary button-sm">Logout</a>
                    </div>
                <?php else: ?>
                    <div>Not logged in</div>
                <?php endif; ?>
            </div>
        </header>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$apiController->isAuthenticated()): ?>
            <div class="login-section">
                <h2>API Authentication</h2>
                <form class="login-form" method="post" action="">
                    <div class="form-group">
                        <label for="username">Username (Email):</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" name="login">Login</button>
                </form>
                
                <div style="margin-top: 20px; text-align: center;">
                    <p><strong>Default credentials:</strong> admin@example.com / admin123</p>
                </div>
            </div>
        <?php else: ?>
            <div class="tabs">
                <div class="tab <?php echo (!isset($_GET['action']) || $_GET['action'] !== 'create') ? 'active' : ''; ?>" onclick="window.location.href='api-integration.php'">
                    Member List
                </div>
                <div class="tab <?php echo (isset($_GET['action']) && $_GET['action'] === 'create') ? 'active' : ''; ?>" onclick="window.location.href='api-integration.php?action=create'">
                    Add New Member
                </div>
            </div>
            
            <?php if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit')): ?>
                <!-- Member Form (Create/Edit) -->
                <div class="member-section">
                    <h2><?php echo $editMember ? 'Edit Member' : 'Add New Member'; ?></h2>
                    <form class="member-form" method="post" action="">
                        <?php if ($editMember): ?>
                            <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($editMember->getId()); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo $editMember ? htmlspecialchars($editMember->getFirstName()) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo $editMember ? htmlspecialchars($editMember->getLastName()) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo $editMember ? htmlspecialchars($editMember->getEmail()) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo $editMember ? htmlspecialchars($editMember->getPhone()) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <textarea id="address" name="address"><?php echo $editMember ? htmlspecialchars($editMember->getAddress()) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="membership_level">Membership Level:</label>
                            <select id="membership_level" name="membership_level">
                                <option value="Standard" <?php echo ($editMember && $editMember->getMembershipLevel() === 'Standard') ? 'selected' : ''; ?>>Standard</option>
                                <option value="Premium" <?php echo ($editMember && $editMember->getMembershipLevel() === 'Premium') ? 'selected' : ''; ?>>Premium</option>
                                <option value="Gold" <?php echo ($editMember && $editMember->getMembershipLevel() === 'Gold') ? 'selected' : ''; ?>>Gold</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_date">Join Date:</label>
                            <input type="date" id="join_date" name="join_date" value="<?php echo $editMember ? htmlspecialchars($editMember->getJoinDate()) : date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="active" <?php echo (!$editMember || $editMember->isActive()) ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                        
                        <?php if ($editMember): ?>
                            <button type="submit" name="update_member">Update Member</button>
                        <?php else: ?>
                            <button type="submit" name="create_member">Create Member</button>
                        <?php endif; ?>
                        
                        <a href="api-integration.php" class="button button-secondary" style="margin-left: 10px;">Cancel</a>
                    </form>
                </div>
            <?php else: ?>
                <!-- Members List -->
                <div class="members-list">
                    <h2>Member List</h2>
                    
                    <form class="search-form" method="get" action="">
                        <input type="text" name="search" placeholder="Search by name or email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit">Search</button>
                        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="api-integration.php" class="button button-secondary" style="margin-left: 10px;">Clear</a>
                        <?php endif; ?>
                    </form>
                    
                    <a href="?action=create" class="button" style="margin-bottom: 20px;">Add New Member</a>
                    
                    <?php if (empty($members)): ?>
                        <p>No members found.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Membership</th>
                                    <th>Join Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member->getId()); ?></td>
                                        <td><?php echo htmlspecialchars($member->getFirstName() . ' ' . $member->getLastName()); ?></td>
                                        <td><?php echo htmlspecialchars($member->getEmail()); ?></td>
                                        <td><?php echo htmlspecialchars($member->getPhone() ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($member->getMembershipLevel()); ?></td>
                                        <td><?php echo htmlspecialchars($member->getJoinDate()); ?></td>
                                        <td><?php echo $member->isActive() ? 'Active' : 'Inactive'; ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo htmlspecialchars($member->getId()); ?>" class="button button-secondary button-sm">Edit</a>
                                            <a href="?action=delete&id=<?php echo htmlspecialchars($member->getId()); ?>" onclick="return confirm('Are you sure you want to delete this member?');" class="button button-danger button-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Simple pagination -->
                        <?php if (!isset($_GET['search'])): ?>
                            <div style="margin-top: 20px; text-align: center;">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="button button-secondary button-sm">Previous</a>
                                <?php endif; ?>
                                
                                <span style="margin: 0 10px;">Page <?php echo $page; ?></span>
                                
                                <?php if (count($members) >= 10): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="button button-secondary button-sm">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <h3>API Status Information</h3>
                <p>Base URL: <?php echo htmlspecialchars(ApiConfig::getBaseUrl()); ?></p>
                <p>Authentication Status: <?php echo $apiController->isAuthenticated() ? '<span style="color: green;">Authenticated</span>' : '<span style="color: red;">Not Authenticated</span>'; ?></p>
                
                <?php if ($apiController->isAuthenticated()): ?>
                    <p>
                        <strong>API Health Check:</strong>
                        <?php 
                        try {
                            $healthStatus = $apiController->getApiClient()->healthCheck();
                            echo '<span style="color: green;">OK</span>';
                        } catch (Exception $e) {
                            echo '<span style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                        }
                        ?>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($apiController->getErrors())): ?>
                    <div style="color: red; margin-top: 10px;">
                        <strong>API Errors:</strong>
                        <ul>
                            <?php foreach ($apiController->getErrors() as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>