<?php
session_start();
include 'db.php';

// Handle user registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    if ($stmt->rowCount() > 0) {
        $registration_error = "Username or email already exists.";
    } else {
        // Insert into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password])) {
            $registration_success = "Registration successful! You can now log in.";
        }
    }
}

// Handle user login
if (isset($_POST['login'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    // Fetch user data from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); // Redirect to the main page after successful login
            exit();
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "No user found with that username.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php"); // Redirect to index after logout
    exit();
}

// Handle delete operation
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Delete the book from the database
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php"); // Redirect to index after deletion
    exit();
}

// Handle update operation
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $description = $_POST['description'];

    // Update the book in the database
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, genre = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $author, $genre, $description, $id]);

    header("Location: index.php"); // Redirect to index after successful update
    exit();
}

// Read operation for books
$stmt = $pdo->query("SELECT * FROM books");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Bookstore</h1>

    <?php if (isset($_SESSION['username'])): ?>
        <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
        <a href="?logout=true" class='btn btn-danger'>Logout</a> 

        <!-- Display Books -->
        <h2 class="mt-4">Books List</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Description</th>
                    <th>Actions</th> <!-- Added Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= htmlspecialchars($book['id']) ?></td>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['genre']) ?></td>
                        <td><?= htmlspecialchars($book['description']) ?></td>

                        <!-- Update and Delete Buttons -->
                        <td>
                            <!-- Update Button -->
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#updateModal<?= htmlspecialchars($book['id']) ?>">Edit</button>

                            <!-- Delete Button -->
                            <a href="?delete=<?= htmlspecialchars($book['id']) ?>" onclick="return confirm('Are you sure you want to delete this book?');" class="btn btn-danger btn-sm">Delete</a>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?= htmlspecialchars($book['id']) ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?= htmlspecialchars($book['id']) ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel<?= htmlspecialchars($book['id']) ?>">Update Book</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                                                <div class="form-group">
                                                    <label for="title">Title:</label>
                                                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="author">Author:</label>
                                                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="genre">Genre:</label>
                                                    <input type="text" name="genre" class="form-control" value="<?= htmlspecialchars($book['genre']) ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="description">Description:</label>
                                                    <textarea name="description" class="form-control" required><?= htmlspecialchars($book['description']) ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="update" class="btn btn-success">Update Book</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td> 
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <!-- Registration Form -->
        <h2>Register</h2>
        <?php if (isset($registration_error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($registration_error) ?></div>
        <?php endif; ?>
        <?php if (isset($registration_success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($registration_success) ?></div>
        <?php endif; ?>
        <form method="POST" class="mb-4">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name='register' class='btn btn-primary'>Register</button> 
        </form>

        <!-- Login Form -->

<h2>Login</h2>

<?php if (isset($login_error)): ?>
<div class='alert alert-danger'><?= htmlspecialchars($login_error) ?></div>

<?php endif; ?>

<form method='POST' class='mb-4'>
<div class='form-group'>
<label for='login_username'>Username:</label>

<input type='text' name='login_username' class='form-control' required>

</div>

<div class='form-group'>
<label for='login_password'>Password:</label>

<input type='password' name='login_password' class='form-control' required>

</div>

<button type='submit' name='login' class='btn btn-success'>Login</button>

</form>


<?php endif; ?>

</div>

<script src="//code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<script src="//cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

<script src="//stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>

