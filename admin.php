<?php
include 'header.php';
include 'db.php';

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock_qty = $_POST['stock_qty'];
    $sku = $_POST['sku'];

    $conn->begin_transaction();
    try {
        $sql = "INSERT INTO product (category_id, name, description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $category_id, $name, $description);
        $stmt->execute();
        $product_id = $stmt->insert_id;

        $sql = "INSERT INTO product_item (product_id, SKU, stock_qty, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isid", $product_id, $sku, $stock_qty, $price);
        $stmt->execute();

        $conn->commit();
        echo "<div class='alert alert-success'>Product added successfully!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock_qty = $_POST['stock_qty'];
    $sku = $_POST['sku'];

    $conn->begin_transaction();
    try {
        $sql = "UPDATE product SET category_id=?, name=?, description=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $category_id, $name, $description, $product_id);
        $stmt->execute();

        $sql = "UPDATE product_item SET SKU=?, stock_qty=?, price=? WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidi", $sku, $stock_qty, $price, $product_id);
        $stmt->execute();

        $conn->commit();
        echo "<div class='alert alert-success'>Product updated successfully!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    $conn->begin_transaction();
    try {
        $sql = "DELETE FROM product_item WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $sql = "DELETE FROM product WHERE product_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $conn->commit();
        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch products
$sql = "SELECT p.product_id, p.name, p.description, c.category_name, pi.SKU, pi.stock_qty, pi.price 
        FROM product p 
        JOIN product_item pi ON p.product_id = pi.product_id
        JOIN product_category c ON p.category_id = c.category_id";
$result = $conn->query($sql);

?>

<h2>Admin Dashboard</h2>

<h3>Add New Product</h3>
<form action="admin.php" method="post">
    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="category_id">Category</label>
        <select class="form-control" id="category_id" name="category_id" required>
            <?php
            $category_sql = "SELECT category_id, category_name FROM product_category";
            $categories = $conn->query($category_sql);
            while ($category = $categories->fetch_assoc()) {
                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="price">Price</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
    </div>
    <div class="form-group">
        <label for="stock_qty">Stock Quantity</label>
        <input type="number" class="form-control" id="stock_qty" name="stock_qty" required>
    </div>
    <div class="form-group">
        <label for="sku">SKU</label>
        <input type="text" class="form-control" id="sku" name="sku" required>
    </div>
    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
</form>

<h3>Product List</h3>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Stock Quantity</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($product = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td><?php echo htmlspecialchars($product['SKU']); ?></td>
                <td><?php echo htmlspecialchars($product['stock_qty']); ?></td>
                <td><?php echo htmlspecialchars($product['price']); ?></td>
                <td>
                    <form action="admin.php" method="post" style="display:inline-block;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                    </form>
                    <button class="btn btn-warning" onclick="populateUpdateForm('<?php echo $product['product_id']; ?>', '<?php echo $product['name']; ?>', '<?php echo $product['description']; ?>', '<?php echo $product['category_id']; ?>', '<?php echo $product['price']; ?>', '<?php echo $product['stock_qty']; ?>', '<?php echo $product['SKU']; ?>')">Update</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Update Product Modal -->
<div class="modal" id="updateProductModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Product</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="admin.php" method="post">
                    <input type="hidden" id="update_product_id" name="product_id">
                    <div class="form-group">
                        <label for="update_name">Product Name</label>
                        <input type="text" class="form-control" id="update_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="update_description">Description</label>
                        <textarea class="form-control" id="update_description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="update_category_id">Category</label>
                        <select class="form-control" id="update_category_id" name="category_id" required>
                            <?php
                            $categories->data_seek(0); // Reset result pointer to the beginning
                            while ($category = $categories->fetch_assoc()) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="update_price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="update_price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label for="update_stock_qty">Stock Quantity</label>
                        <input type="number" class="form-control" id="update_stock_qty" name="stock_qty" required>
                    </div>
                    <div class="form-group">
                        <label for="update_sku">SKU</label>
                        <input type="text" class="form-control" id="update_sku" name="sku" required>
                    </div>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function populateUpdateForm(productId, name, description, categoryId, price, stockQty, sku) {
    document.getElementById('update_product_id').value = productId;
    document.getElementById('update_name').value = name;
    document.getElementById('update_description').value = description;
    document.getElementById('update_category_id').value = categoryId;
    document.getElementById('update_price').value = price;
    document.getElementById('update_stock_qty').value = stockQty;
    document.getElementById('update_sku').value = sku;

    $('#updateProductModal').modal('show');
}
</script>

<?php include 'footer.php'; ?>
