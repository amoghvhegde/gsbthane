<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Database\DatabaseConnection;
use App\Database\SchemaBuilder;
use App\Database\DataImporter;
use App\Models\User;
use App\Models\Membership;

// Default action is 'home'
$action = $_GET['action'] ?? 'home';

try {
    // Initialize database connection and create tables if they don't exist
    $db = DatabaseConnection::getInstance();
    $schemaBuilder = new SchemaBuilder();
    $schemaBuilder->createTables();
    
    // Handle actions
    switch ($action) {
        case 'import':
            handleImport();
            break;
        case 'list-users':
            handleListUsers();
            break;
        case 'list-members':
            handleListMembers();
            break;
        case 'view-member':
            handleViewMember();
            break;
        default:
            handleHome();
    }
} catch (Exception $e) {
    renderHeader('Error');
    echo "<div class='error-message'>Error: {$e->getMessage()}</div>";
    renderFooter();
}

// Action handlers
function handleHome() {
    renderHeader('GSB Mandal Management System');
    ?>    
    <div class="welcome">
        <h2>Welcome to GSB Mandal Management System</h2>
        <p>This system helps manage membership data and operations for G.S.B. Mandal, Thane.</p>
    </div>
    
    <div class="steps">
        <div class="step">
            <h3>1. Import Data</h3>
            <p>Import member data from CSV files to populate the database.</p>
            <a href="?action=import" class="button">Go to Import</a>
        </div>
        
        <div class="step">
            <h3>2. User Management</h3>
            <p>View and manage user accounts in the system.</p>
            <a href="?action=list-users" class="button">Manage Users</a>
        </div>
        
        <div class="step">
            <h3>3. Member Management</h3>
            <p>View and manage membership details and applications.</p>
            <a href="?action=list-members" class="button">Manage Members</a>
        </div>
    </div>
    <?php
    renderFooter();
}

function handleImport() {
    $message = '';
    $messageType = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_files'])) {
        try {
            $uploads = $_FILES['csv_files'];
            $uploadDir = __DIR__ . '/../uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $membersFile = '';
            $addressesFile = '';
            $consolidatedFile = '';
            
            // Process each uploaded file
            for ($i = 0; $i < count($uploads['name']); $i++) {
                if ($uploads['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $uploads['tmp_name'][$i];
                    $fileName = basename($uploads['name'][$i]);
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Identify file type based on name
                        if (stripos($fileName, 'FINAL-MEMBER') !== false) {
                            $membersFile = $filePath;
                        } else if (stripos($fileName, 'FINAL-ADDRESS') !== false) {
                            $addressesFile = $filePath;
                        } else if (stripos($fileName, 'CONSOLIDATED') !== false) {
                            $consolidatedFile = $filePath;
                        }
                    }
                }
            }
            
            // Perform import
            $dataImporter = new DataImporter();
            $importResult = $dataImporter->importMemberData(
                $membersFile, 
                $addressesFile, 
                $consolidatedFile
            );
            
            if ($importResult) {
                $message = 'Data imported successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error during data import. Please check the files and try again.';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Import failed: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    renderHeader('Import Data');
    
    if (!empty($message)) {
        echo "<div class='{$messageType}-message'>{$message}</div>";
    }
    
    ?>    
    <h2>Import Member Data</h2>
    <p>Upload CSV files to import member data into the system.</p>
    
    <form action="?action=import" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="csv_files">Select CSV Files:</label>
            <input type="file" id="csv_files" name="csv_files[]" multiple accept=".csv" required>
            <small>Select all three files: FINAL-MEMBER.csv, FINAL-ADDRESS.csv, and CONSOLIDATED.csv</small>
        </div>
        
        <input type="submit" value="Import Data" class="button">
    </form>
    
    <h3>Expected File Structure</h3>
    <p>The import expects the following CSV files:</p>
    <ul>
        <li><strong>FINAL-MEMBER.csv</strong>: Contains basic member information</li>
        <li><strong>FINAL-ADDRESS.csv</strong>: Contains address information for members</li>
        <li><strong>CONSOLIDATED.csv</strong>: Contains additional details and supplementary information</li>
    </ul>
    <?php
    renderFooter();
}

function handleListUsers() {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    
    if (!empty($search)) {
        $users = User::search($search, $limit, $offset);
        $totalUsers = count(User::search($search));
    } else {
        $users = User::findAll($limit, $offset);
        $totalUsers = User::count();
    }
    
    $totalPages = ceil($totalUsers / $limit);
    
    renderHeader('User Management');
    ?>
    <h2>User Management</h2>
    
    <div class="search-form">
        <form action="" method="get">
            <input type="hidden" name="action" value="list-users">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="button">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?action=list-users" class="button">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Type</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6">No users found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getFullName()) ?></td>
                        <td><?= htmlspecialchars($user->getEmail() ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($user->getMobileNo()) ?></td>
                        <td><?= $user->getUserType() === 'M' ? 'Member' : 'Non-Member' ?></td>
                        <td><?= date('Y-m-d', strtotime($user->getCreatedAt())) ?></td>
                        <td>
                            <?php if ($user->getUserType() === 'M'): ?>
                                <a href="?action=view-member&id=<?= $user->getId() ?>" class="button">View Membership</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?action=list-users&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="button">Previous</a>
        <?php endif; ?>
        
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        
        <?php if ($page < $totalPages): ?>
            <a href="?action=list-users&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="button">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
    renderFooter();
}

function handleListMembers() {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    
    if (!empty($search)) {
        $members = Membership::search($search, $limit, $offset);
        $totalMembers = count(Membership::search($search));
    } else {
        $members = Membership::findAll($limit, $offset);
        $totalMembers = Membership::count();
    }
    
    $totalPages = ceil($totalMembers / $limit);
    
    renderHeader('Member Management');
    ?>
    <h2>Member Management</h2>
    
    <div class="search-form">
        <form action="" method="get">
            <input type="hidden" name="action" value="list-members">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search members..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="button">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="?action=list-members" class="button">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Membership Type</th>
                <th>Status</th>
                <th>Application Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr>
                    <td colspan="5">No members found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <?php $user = $member->getUser(); ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getFullName()) ?></td>
                        <td><?= htmlspecialchars($member->getMembershipType()) ?></td>
                        <td><?= htmlspecialchars($member->getStatus()) ?></td>
                        <td><?= date('Y-m-d', strtotime($member->getApplicationDate() ?? 'now')) ?></td>
                        <td>
                            <a href="?action=view-member&id=<?= $user->getId() ?>" class="button">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?action=list-members&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="button">Previous</a>
        <?php endif; ?>
        
        <span>Page <?= $page ?> of <?= $totalPages ?></span>
        
        <?php if ($page < $totalPages): ?>
            <a href="?action=list-members&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="button">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
    renderFooter();
}

function handleViewMember() {
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        header('Location: ?action=list-members');
        exit;
    }
    
    $user = User::findById($id);
    
    if (!$user) {
        renderHeader('Error');
        echo "<div class='error-message'>User not found</div>";
        echo "<a href='?action=list-members' class='button'>Back to Members</a>";
        renderFooter();
        return;
    }
    
    $membership = Membership::findByUserId($id);
    
    if (!$membership) {
        renderHeader('Error');
        echo "<div class='error-message'>Membership details not found</div>";
        echo "<a href='?action=list-users' class='button'>Back to Users</a>";
        renderFooter();
        return;
    }
    
    renderHeader('Member Details');
    ?>
    <h2>Member Details</h2>
    
    <div class="member-details">
        <h3>Personal Information</h3>
        <table>
            <tr>
                <th>Full Name</th>
                <td><?= htmlspecialchars($user->getFullName()) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($user->getEmail() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Mobile</th>
                <td><?= htmlspecialchars($user->getMobileNo()) ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?= htmlspecialchars($membership->getGender() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td><?= $membership->getDateOfBirth() ? date('Y-m-d', strtotime($membership->getDateOfBirth())) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Marital Status</th>
                <td><?= htmlspecialchars($membership->getMaritalStatus() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Number of Kids</th>
                <td><?= $membership->getNumberOfKids() ?? 'N/A' ?></td>
            </tr>
            <tr>
                <th>Occupation</th>
                <td><?= htmlspecialchars($membership->getOccupation() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Qualification</th>
                <td><?= htmlspecialchars($membership->getQualification() ?? 'N/A') ?></td>
            </tr>
        </table>
        
        <h3>Address Information</h3>
        <table>
            <tr>
                <th>Postal Address</th>
                <td><?= nl2br(htmlspecialchars($membership->getPostalAddress() ?? 'N/A')) ?></td>
            </tr>
            <tr>
                <th>PIN Code</th>
                <td><?= htmlspecialchars($membership->getPinCode() ?? 'N/A') ?></td>
            </tr>
        </table>
        
        <h3>Cultural Information</h3>
        <table>
            <tr>
                <th>Gotra</th>
                <td><?= htmlspecialchars($membership->getGotra() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Kuladevata</th>
                <td><?= htmlspecialchars($membership->getKuladevata() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Math</th>
                <td><?= htmlspecialchars($membership->getMath() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Native Place</th>
                <td><?= htmlspecialchars($membership->getNativePlace() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Other GSB Memberships</th>
                <td><?= htmlspecialchars($membership->getOtherGsbMemberships() ?? 'N/A') ?></td>
            </tr>
        </table>
        
        <h3>Membership Information</h3>
        <table>
            <tr>
                <th>Membership Type</th>
                <td><?= htmlspecialchars($membership->getMembershipType()) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= htmlspecialchars($membership->getStatus()) ?></td>
            </tr>
            <tr>
                <th>Application Date</th>
                <td><?= $membership->getApplicationDate() ? date('Y-m-d', strtotime($membership->getApplicationDate())) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Approval Date</th>
                <td><?= $membership->getApprovalDate() ? date('Y-m-d', strtotime($membership->getApprovalDate())) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Introducer Name</th>
                <td><?= htmlspecialchars($membership->getIntroducerName() ?? 'N/A') ?></td>
            </tr>
        </table>
        
        <h3>ID Information</h3>
        <table>
            <tr>
                <th>Aadhar Number</th>
                <td><?= htmlspecialchars($membership->getAadharNumber() ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>PAN Number</th>
                <td><?= htmlspecialchars($membership->getPanNumber() ?? 'N/A') ?></td>
            </tr>
        </table>
    </div>
    
    <div class="actions">
        <a href="?action=list-members" class="button">Back to Members</a>
    </div>
    <?php
    renderFooter();
}

// Helper functions for rendering
function renderHeader($title) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> | GSB Mandal Management</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
            <h1>GSB Mandal Management System</h1>
            <nav>
                <ul>
                    <li><a href="?">Home</a></li>
                    <li><a href="?action=import">Import Data</a></li>
                    <li><a href="?action=list-users">Users</a></li>
                    <li><a href="?action=list-members">Members</a></li>
                </ul>
            </nav>
        </header>
        <main>
    <?php
}

function renderFooter() {
    ?>
        </main>
        <footer>
            <p>&copy; <?= date('Y') ?> GSB Mandal Management System</p>
        </footer>
        <script src="js/script.js"></script>
    </body>
    </html>
    <?php
}