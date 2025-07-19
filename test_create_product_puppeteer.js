const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
    const page = await browser.newPage();

    // Go to the login page
    await page.goto('https://test-website.great-site.net/admin_login.php');

    // Fill in the login form
    await page.type('input[name="username"]', 'admin');
    await page.type('input[name="password"]', 'password');

    // Click the login button
    await page.click('button[type="submit"]');

    // Wait for navigation to the dashboard
    await page.waitForNavigation();

    // Go to the add product page
    await page.goto('https://test-website.great-site.net/add_product.php');

    // Fill out the form
    await page.type('input[name="name"]', 'Test Product Puppeteer');
    await page.type('textarea[name="description"]', 'This is a test product from puppeteer.');
    await page.type('textarea[name="longDescription"]', 'This is a long description for the test product from puppeteer.');
    await page.type('input[name="price"]', '12.99');
    await page.select('select[name="category"]', 'software');

    // Upload an image
    const imagePath = 'test_image.png';
    const imageContent = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    require('fs').writeFileSync(imagePath, Buffer.from(imageContent, 'base64'));
    const inputUploadHandle = await page.$('input[type=file]');
    await inputUploadHandle.uploadFile(imagePath);

    // Click the "Add Product" button
    await page.click('button[type="submit"]');

    // Wait for navigation to the product list
    await page.waitForNavigation();

    console.log('Product added successfully!');

    await browser.close();
})();
