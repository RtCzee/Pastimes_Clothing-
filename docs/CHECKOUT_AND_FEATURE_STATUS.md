# Pastimes Feature Status and Checkout Design

## Current Feature Status

### Already implemented
- Rating and review: not implemented
- Product browsing with search and filters: implemented in Shop.php and app.js
- Wishlist and price drop alert: not implemented
- Login and registration appreciation messages: implemented through auth success messaging

### Shopping and checkout status
- Add to cart: implemented with localStorage
- Cart quantity edit: implemented with plus/minus controls
- Continue shopping: implemented from the cart page
- Checkout reference number: implemented in the new checkout flow
- Checkout database entry: implemented with tblCheckout and cart line inserts
- Cart reset after checkout: implemented by clearing the stored cart state

## Checkout Design

### Goals
1. Validate the cart server-side.
2. Create a checkout reference number.
3. Store one checkout summary row in the database.
4. Store the order lines tied to that reference.
5. Clear the cart state after success.
6. Return the user to the login page with a success message.

### Data Flow
1. The cart page sends the current localStorage cart to checkout.php.
2. checkout.php verifies the session and rebuilds the cart contents from the product source.
3. The server computes subtotal, shipping, total, and a reference number.
4. The server inserts a row into tblCheckout.
5. The server inserts the item rows into tblAorder.
6. The client clears the cart storage and redirects to login.php.

### Open Gaps
- Wishlist and price-drop alerts still need their own data model and UI.
- Ratings and reviews still need a review table, submission form, and display component.
- The current checkout flow records orders, but it does not integrate with a real payment gateway.