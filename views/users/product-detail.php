<?php
/**
 * Users: Product Detail
 *
 * @package shopgauyeu
 * @author quocviet
 * @since 0.0.1
 */
?>


<?php include __DIR__ . '/../includes/global.php'; ?>
<?php include __DIR__ . '/header.php'; ?>

<div class="container">
    <div class="row">

        <div class="col-md-6">
            <div class="product-images">
                <div class="main-image">
                    <img src="path/to/main-image.jpg" alt="Main Product Image" class="img-fluid">
                </div>
                <div class="image-gallery">
                    <img src="path/to/image1.jpg" alt="Image 1" class="img-thumbnail">
                    <img src="path/to/image2.jpg" alt="Image 2" class="img-thumbnail">
                    <img src="path/to/image3.jpg" alt="Image 3" class="img-thumbnail">
                    <img src="path/to/image4.jpg" alt="Image 4" class="img-thumbnail">
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="product-details">
                <h1>Blindbox official SAMUEL Dopamine Rabbit</h1>
                <p class="price">300,000đ</p>
                <div class="color-options">
                    <label for="color">Màu sắc:</label>
                    <select id="color" name="color">
                        <option value="black">Black</option>
                        <option value="brown">Brown</option>
                        <option value="yellow">Yellow</option>
                        <option value="purple">Purple</option>
                        <option value="green">Green</option>
                        <option value="pink">Pink</option>
                    </select>
                </div>
                <div class="quantity">
                    <label for="quantity">Số lượng:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1">
                </div>
                <button class="btn btn-primary">Thêm vào giỏ hàng</button>
                <button class="btn btn-secondary">Mua ngay</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
