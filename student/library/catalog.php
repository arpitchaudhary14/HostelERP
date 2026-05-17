<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../library/includes/library_functions.php";

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

if (isset($_POST['borrow_book'])) {
    $book_id = $_POST['book_id'];
    $result = request_book($conn, $user_id, $book_id);
    if ($result === true) {
        $message = "Borrow request sent successfully! Wait for Admin approval.";
        $message_type = "success";
    } else {
        $message = $result;
        $message_type = "danger";
    }
}

$category_id = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$books = get_library_books($conn, $category_id, $search);
$categories = get_library_categories($conn);

include("../../header.php");
?>

<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold text-gradient mb-0">Book Catalog</h2>
                <p class="text-muted mb-0">Explore thousands of titles in our library.</p>
            </div>
            <form class="d-flex gap-2 flex-grow-1 max-w-500" method="GET">
                <input type="text" name="search" class="form-control rounded-pill px-4" placeholder="Search by title, author or ISBN..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-gradient rounded-pill px-4">Search</button>
            </form>
        </div>
    </div>

    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3 mb-4">
            <div class="glass-card-light h-100">
                <h5 class="fw-bold mb-3">Categories</h5>
                <div class="list-group list-group-flush rounded-3 overflow-hidden">
                    <a href="catalog.php" class="list-group-item list-group-item-action <?= !$category_id ? 'active' : '' ?>">All Categories</a>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <a href="catalog.php?category=<?= $cat['id'] ?>" class="list-group-item list-group-item-action <?= $category_id == $cat['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="col-md-9">
            <div class="row g-4">
                <?php while($book = mysqli_fetch_assoc($books)): ?>
                <div class="col-sm-6 col-lg-4 reveal">
                    <div class="glass-card-light book-card h-100 p-0 overflow-hidden">
                        <div class="book-cover-container" style="background-image: url('/WebTechProject/assets/images/<?= $book['cover_image'] ?>')">
                            <div class="book-category-badge"><?= htmlspecialchars($book['category_name']) ?></div>
                        </div>
                        <div class="p-3">
                            <h6 class="fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($book['title']) ?>"><?= htmlspecialchars($book['title']) ?></h6>
                            <p class="small text-muted mb-1">By <?= htmlspecialchars($book['author']) ?></p>
                            <?php if (!empty($book['description'])): ?>
                            <p class="small text-muted mb-2 book-desc" title="<?= htmlspecialchars($book['description']) ?>">
                                <?= htmlspecialchars(mb_strimwidth($book['description'], 0, 80, '...')) ?>
                            </p>
                            <?php else: ?>
                            <p class="small mb-2" style="min-height:18px;"></p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small <?= $book['available_copies'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                    <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?> Available
                                </span>
                                <?php if($book['available_copies'] > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                    <button type="submit" name="borrow_book" class="btn btn-sm btn-outline-primary rounded-pill px-3">Borrow</button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary rounded-pill px-3 disabled">Waiting List</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($books) == 0): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search h1 text-muted d-block mb-3"></i>
                    <p class="text-muted">No books found matching your criteria.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.max-w-500 { max-width: 500px; }
.book-desc {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-style: italic;
    opacity: 0.8;
    cursor: help;
}
.book-card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
}
.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.book-cover-container {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-color: #f8f9fa;
    position: relative;
}
.book-category-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255,255,255,0.9);
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--accent-primary);
}
.list-group-item.active {
    background: var(--gradient-primary);
    border-color: transparent;
}
</style>

<?php include("../../footer.php"); ?>
