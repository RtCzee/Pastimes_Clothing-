(() => {
  // Store cart data in localStorage
  const CART_KEY = "pastimes-cart";
  const WISHLIST_KEY = "pastimes-wishlist";
  const PRICE_ALERT_KEY = "pastimes-price-alerts";
  const products = Array.isArray(window.PASTIMES_PRODUCTS) ? window.PASTIMES_PRODUCTS : [];

  // Find a product by ID
  const getProductById = (id) => products.find((product) => String(product.id) === String(id));

  // Read cart from localStorage
  const readCart = () => {
    try {
      const raw = window.localStorage.getItem(CART_KEY);
      const parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  };

  // Save cart to localStorage and refresh UI
  const writeCart = (cart) => {
    window.localStorage.setItem(CART_KEY, JSON.stringify(cart));
    refreshCartCount();
    renderCartPage();
  };

  // Get cart items with full product details
  const cartItemsExpanded = () =>
    readCart()
      .map((item) => {
        const product = getProductById(item.id);
        if (!product) {
          return null;
        }
        return { ...product, quantity: item.quantity };
      })
      .filter(Boolean);

  // Update cart count badge
  const refreshCartCount = () => {
    const total = readCart().reduce((sum, item) => sum + item.quantity, 0);
    document.querySelectorAll("[data-cart-count]").forEach((element) => {
      element.textContent = String(total);
      element.classList.toggle("is-hidden", total === 0);
    });
  };

  // Add a product to the cart (or increase quantity if exists)
  const addToCart = (productId) => {
    const cart = readCart();
    const existing = cart.find((item) => String(item.id) === String(productId));
    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({ id: String(productId), quantity: 1 });
    }
    writeCart(cart);
  };

  // Update quantity of a cart item
  const updateCartQuantity = (productId, quantity) => {
    const next = readCart()
      .map((item) => (String(item.id) === String(productId) ? { ...item, quantity } : item))
      .filter((item) => item.quantity > 0);
    writeCart(next);
  };

  // Remove a product from the cart
  const removeFromCart = (productId) => {
    writeCart(readCart().filter((item) => String(item.id) !== String(productId)));
  };

  // Clear all items from cart
  const clearCart = () => {
    writeCart([]);
  };

  const readJsonStorage = (key) => {
    try {
      const raw = window.localStorage.getItem(key);
      const parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  };

  const writeJsonStorage = (key, value) => {
    window.localStorage.setItem(key, JSON.stringify(value));
  };

  const readWishlist = () => readJsonStorage(WISHLIST_KEY);
  const writeWishlist = (wishlist) => {
    writeJsonStorage(WISHLIST_KEY, wishlist);
    refreshWishlistCount();
    syncWishlistButtons();
    renderWishlistPage();
  };

  const readPriceAlerts = () => readJsonStorage(PRICE_ALERT_KEY);
  const writePriceAlerts = (alerts) => {
    writeJsonStorage(PRICE_ALERT_KEY, alerts);
    updatePriceAlertState();
  };

  const refreshWishlistCount = () => {
    const total = readWishlist().length;
    document.querySelectorAll("[data-wishlist-count]").forEach((element) => {
      element.textContent = String(total);
      element.classList.toggle("is-hidden", total === 0);
    });
  };

  const isWishlisted = (productId) => readWishlist().some((item) => String(item.id) === String(productId));

  const syncWishlistButtons = () => {
    document.querySelectorAll("[data-wishlist-toggle]").forEach((button) => {
      const id = button.getAttribute("data-wishlist-toggle");
      const active = isWishlisted(id);
      button.classList.toggle("is-active", active);
      button.setAttribute("aria-pressed", active ? "true" : "false");
      button.innerHTML = active
        ? "<svg class='icon icon--tiny' viewBox='0 0 24 24' fill='currentColor' aria-hidden='true'><path d='m12 21-1.4-1.2C5.4 15 2 11.9 2 8a4 4 0 0 1 7-2.6A4 4 0 0 1 16 8c0 3.9-3.4 7-8.6 11.8Z'/></svg> Saved"
        : "<svg class='icon icon--tiny' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'><path d='m12 21-1.4-1.2C5.4 15 2 11.9 2 8a4 4 0 0 1 7-2.6A4 4 0 0 1 16 8c0 3.9-3.4 7-8.6 11.8Z'/></svg> Save";
    });
  };

  const toggleWishlist = (productId) => {
    const wishlist = readWishlist();
    const index = wishlist.findIndex((item) => String(item.id) === String(productId));
    if (index >= 0) {
      wishlist.splice(index, 1);
    } else {
      wishlist.push({ id: String(productId) });
    }
    writeWishlist(wishlist);
  };

  const renderWishlistPage = () => {
    const grid = document.querySelector("[data-wishlist-grid]");
    if (!grid) {
      return;
    }

    const empty = document.querySelector("[data-wishlist-empty]");
    const saved = readWishlist()
      .map((item) => getProductById(item.id))
      .filter(Boolean);

    if (saved.length === 0) {
      grid.innerHTML = "";
      if (empty) empty.classList.remove("is-hidden");
      return;
    }

    if (empty) empty.classList.add("is-hidden");
    grid.innerHTML = saved
      .map(
        (item) => `
          <article class="product-card">
            <a class="product-card__image-link" href="Product.php?id=${item.id}">
              <div class="product-card__image-wrap">
                <img class="product-card__image" src="${item.image}" alt="${item.name}">
              </div>
            </a>
            <div class="product-card__body">
              <div class="product-card__top">
                <div>
                  <a class="product-card__title-link" href="Product.php?id=${item.id}"><h3 class="product-card__title">${item.name}</h3></a>
                  <p class="product-card__meta">${item.category} &middot; Size ${item.size}</p>
                </div>
              </div>
              <div class="product-card__bottom">
                <div class="product-card__price-wrap"><span class="product-card__price">${formatPrice(item.price)}</span></div>
                <button class="button button--ghost button--small" type="button" data-wishlist-toggle="${item.id}">Saved</button>
              </div>
            </div>
          </article>
        `
      )
      .join("");

    syncWishlistButtons();
  };
  //what this does is it checks if there is a price alert set for the current product and updates the status text and target price input accordingly. It also compares the current product price with the alert target price to show if the alert is "Triggered" or just "Saved". This function is called after saving a price alert and on page load to ensure the UI reflects the correct state.
  const updatePriceAlertState = () => {
    const statusNode = document.querySelector("[data-price-alert-status]");
    const form = document.querySelector("[data-price-alert-form]");
    const triggerButton = document.querySelector("[data-price-alert-save]");
    if (!statusNode || !form || !triggerButton) {
      return;
    }
      //this retrieves the product ID from the save button's data attribute, finds the corresponding product, and checks if there is an existing price alert for that product. If an alert exists, it updates the target price input and status text based on whether the current price meets the alert condition. If no alert exists, it sets the status to "Inactive".
    const productId = triggerButton.getAttribute("data-price-alert-save");
    const targetInput = form.querySelector("[data-price-alert-target]");
    const product = getProductById(productId);
    if (!product || !targetInput) {
      return;
    }
      //this part reads the saved price alerts from localStorage, finds if there is an alert for the current product, and updates the UI accordingly. If the product price is less than or equal to the target price, it shows "Triggered"; otherwise, it shows "Saved". If no alert is found, it shows "Inactive".
    const alerts = readPriceAlerts();
    const alert = alerts.find((item) => String(item.id) === String(productId));
    if (alert) {
      targetInput.value = String(alert.targetPrice);
      statusNode.textContent = Number(product.price) <= Number(alert.targetPrice) ? "Triggered" : "Saved";
    } else {
      statusNode.textContent = "Inactive";
    }
  };
    //this function initializes the wishlist toggle buttons on product cards and the wishlist page. It adds click event listeners to all buttons with the data attribute "data-wishlist-toggle" to call the toggleWishlist 
    // function when clicked. It also calls syncWishlistButtons to update the button states based on the current wishlist and refreshWishlistCount to update the wishlist count badge in the UI.
  const initWishlistButtons = () => {
    document.querySelectorAll("[data-wishlist-toggle]").forEach((button) => {
      button.addEventListener("click", () => toggleWishlist(button.getAttribute("data-wishlist-toggle")));
    });
    syncWishlistButtons();
    refreshWishlistCount();
  };
      // this function initializes the wishlist page by checking if the 
      // wishlist grid exists on the page. If it does, it renders the wishlist items and sets up an event listener for clicks on wishlist toggle buttons 
      // to re-render the wishlist when items are added or removed. This ensures that the wishlist page reflects the current state of the user's saved items.
  const initWishlistPage = () => {
    if (!document.querySelector("[data-wishlist-grid]")) {
      return;
    }
      //  then it calls renderWishlistPage to display the current wishlist items and sets up a click event listener on the document to listen for any clicks on elements with the 
      // data attribute "data-wishlist-toggle". When such a button is clicked, it re-renders the wishlist page to reflect any changes made to the wishlist.
    renderWishlistPage();
    document.addEventListener("click", (event) => {
      const button = event.target.closest("[data-wishlist-toggle]");
      if (button) {
        renderWishlistPage();
      }
    });
  };    // this function initiaizes the price alert from the product details page.

  const initPriceAlerts = () => {
    const form = document.querySelector("[data-price-alert-form]");
    const triggerButton = document.querySelector("[data-price-alert-save]");
    if (!form || !triggerButton) {
      return;
    }
        // this event/ form submission handler preventsm
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const productId = triggerButton.getAttribute("data-price-alert-save");
      const targetInput = form.querySelector("[data-price-alert-target]");
      if (!productId || !targetInput) {
        return;
      }
        //this part retrieves the target price from the input field and validates it 
      const targetPrice = Number(targetInput.value);
      if (!Number.isFinite(targetPrice) || targetPrice <= 0) {
        window.alert("Please enter a valid target price.");
        return;
      }
        // 
      const alerts = readPriceAlerts().filter((item) => String(item.id) !== String(productId));
      alerts.push({ id: String(productId), targetPrice });
      writePriceAlerts(alerts);
      updatePriceAlertState();
      window.alert("Price alert saved. Reload this page after a price change to see the status update.");
    });

    updatePriceAlertState();
  };

  // Format number as South African Rand currency
  const formatPrice = (value) =>
    `R${new Intl.NumberFormat("en-ZA", { maximumFractionDigits: 0 }).format(value)}`;

  // Toggle mobile menu visibility
  const initMenu = () => {
    const toggle = document.querySelector("[data-menu-toggle]");
    const nav = document.querySelector("[data-mobile-nav]");
    if (!toggle || !nav) {
      return;
    }

    toggle.addEventListener("click", () => {
      const isOpen = toggle.classList.toggle("is-open");
      nav.classList.toggle("is-open", isOpen);
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
  };

  // Prevent form submission for demo forms
  const initSimpleForms = () => {
    document.querySelectorAll("[data-simple-form]").forEach((form) => {
      form.addEventListener("submit", (event) => {
        event.preventDefault();
      });
    });
  };

  // Setup add to cart buttons on product cards and detail page
  const initAddToCartButtons = () => {
    document.querySelectorAll("[data-add-to-cart]").forEach((button) => {
      button.addEventListener("click", () => {
        addToCart(button.getAttribute("data-add-to-cart"));
      });
    });

    const detailButton = document.querySelector("[data-product-add]");
    const detailLabel = document.querySelector("[data-product-add-label]");
    if (detailButton && detailLabel) {
      detailButton.addEventListener("click", () => {
        addToCart(detailButton.getAttribute("data-product-add"));
        detailButton.disabled = true;
        detailLabel.textContent = "Added to Cart";
        window.setTimeout(() => {
          detailButton.disabled = false;
          detailLabel.textContent = "Add to Cart";
        }, 2000);
      });
    }
  };

  // Handle product image gallery clicks
  const initGallery = () => {
    const mainImage = document.querySelector("[data-product-main-image]");
    if (!mainImage) {
      return;
    }

    document.querySelectorAll("[data-product-thumb]").forEach((button) => {
      button.addEventListener("click", () => {
        document.querySelectorAll("[data-product-thumb]").forEach((item) => item.classList.remove("is-active"));
        button.classList.add("is-active");
        mainImage.src = button.getAttribute("data-image-src");
        mainImage.alt = button.getAttribute("data-image-alt");
      });
    });
  };

  // Render cart page with items and calculate totals
  const renderCartPage = () => {
    const cartPage = document.querySelector("[data-cart-page]");
    const emptyState = document.querySelector("[data-cart-empty]");
    const itemsWrap = document.querySelector("[data-cart-items]");
    if (!cartPage || !emptyState || !itemsWrap) {
      return;
    }

    const items = cartItemsExpanded();
    if (items.length === 0) {
      cartPage.classList.add("is-hidden");
      emptyState.classList.remove("is-hidden");
      itemsWrap.innerHTML = "";
      return;
    }

    cartPage.classList.remove("is-hidden");
    emptyState.classList.add("is-hidden");

    itemsWrap.innerHTML = items
      .map(
        (item) => `
          <article class="cart-item">
            <a class="cart-item__image" href="Product.php?id=${item.id}">
              <img src="${item.image}" alt="${item.name}">
            </a>
            <div class="cart-item__content">
              <div class="cart-item__top">
                <div>
                  <a href="Product.php?id=${item.id}"><strong>${item.name}</strong></a>
                  <p class="cart-item__meta">Size: ${item.size}</p>
                  <p class="cart-item__meta">Condition: ${item.condition}</p>
                </div>
                <button class="text-button" type="button" data-remove-cart-item="${item.id}">Remove</button>
              </div>
              <div class="cart-item__bottom">
                <div class="quantity-control">
                  <button type="button" data-cart-minus="${item.id}">-</button>
                  <span>${item.quantity}</span>
                  <button type="button" data-cart-plus="${item.id}">+</button>
                </div>
                <div>
                  <strong>${formatPrice(item.price * item.quantity)}</strong>
                  ${item.quantity > 1 ? `<p class="cart-item__meta">${formatPrice(item.price)} each</p>` : ""}
                </div>
              </div>
            </div>
          </article>
        `
      )
      .join("");

    const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const shipping = subtotal >= 1500 ? 0 : 150;
    const total = subtotal + shipping;

    const subtotalNode = document.querySelector("[data-cart-subtotal]");
    const shippingNode = document.querySelector("[data-cart-shipping]");
    const totalNode = document.querySelector("[data-cart-total]");
    const shippingNoteNode = document.querySelector("[data-cart-shipping-note]");

    if (subtotalNode) subtotalNode.textContent = formatPrice(subtotal);
    if (shippingNode) shippingNode.textContent = shipping === 0 ? "Free" : formatPrice(shipping);
    if (totalNode) totalNode.textContent = formatPrice(total);
    if (shippingNoteNode) shippingNoteNode.classList.toggle("is-hidden", shipping === 0);

    itemsWrap.querySelectorAll("[data-remove-cart-item]").forEach((button) => {
      button.addEventListener("click", () => removeFromCart(button.getAttribute("data-remove-cart-item")));
    });

    itemsWrap.querySelectorAll("[data-cart-minus]").forEach((button) => {
      button.addEventListener("click", () => {
        const id = button.getAttribute("data-cart-minus");
        const current = readCart().find((item) => String(item.id) === String(id));
        if (current) updateCartQuantity(id, current.quantity - 1);
      });
    });

    itemsWrap.querySelectorAll("[data-cart-plus]").forEach((button) => {
      button.addEventListener("click", () => {
        const id = button.getAttribute("data-cart-plus");
        const current = readCart().find((item) => String(item.id) === String(id));
        if (current) updateCartQuantity(id, current.quantity + 1);
      });
    });
  };

  // Setup clear cart button
  const initCartActions = () => {
    document.querySelectorAll("[data-clear-cart]").forEach((button) => {
      button.addEventListener("click", clearCart);
    });
  };

  // Submit the cart to the server and clear it after checkout
  const initCheckoutFlow = () => {
    const button = document.querySelector("[data-checkout-button]");
    if (!button) {
      return;
    }

    button.addEventListener("click", async () => {
      const items = readCart();
      if (items.length === 0) {
        window.location.href = "login.php";
        return;
      }

      const originalLabel = button.textContent;
      button.disabled = true;
      button.textContent = "Checking out...";

      try {
        const response = await window.fetch("checkout.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ items }),
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok || !payload.success) {
          const message = payload.message || "Checkout could not be completed.";
          window.alert(message);
          return;
        }

        writeCart([]);
        window.location.href = payload.redirectUrl || `login.php?checkout=success&reference=${encodeURIComponent(payload.reference || "")}`;
      } catch (error) {
        window.alert("Checkout failed. Please try again.");
      } finally {
        button.disabled = false;
        button.textContent = originalLabel;
      }
    });
  };

  // Handle contact form submission and feedback
  const initContactForm = () => {
    const form = document.querySelector("[data-contact-form]");
    const label = document.querySelector("[data-contact-label]");
    if (!form || !label) {
      return;
    }

    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const button = form.querySelector("[data-contact-submit]");
      if (!button) {
        return;
      }
      button.disabled = true;
      label.textContent = "Sending...";
      window.setTimeout(() => {
        label.textContent = "Message Sent!";
        window.setTimeout(() => {
          label.textContent = "Send Message";
          button.disabled = false;
          form.reset();
        }, 3000);
      }, 1000);
    });
  };

  // Setup product filtering and sorting
  // Toggle user menu dropdown
  const initUserMenu = () => {
    const toggle = document.querySelector('[data-user-menu-toggle]');
    const dropdown = document.querySelector('[data-user-dropdown]');
    if (!toggle || !dropdown) {
      return;
    }

    toggle.addEventListener('click', () => {
      dropdown.classList.toggle('is-open');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('is-open');
      }
    });
  };

  const initShopFilters = () => {
    const grid = document.querySelector("[data-shop-grid]");
    if (!grid) {
      return;
    }

    const cards = Array.from(grid.querySelectorAll(".product-card"));
    const categoryControls = Array.from(document.querySelectorAll('[data-filter-control="category"]'));
    const sizeControls = Array.from(document.querySelectorAll('[data-filter-control="size"]'));
    const conditionControls = Array.from(document.querySelectorAll('[data-filter-control="condition"]'));
    const sortControl = document.querySelector("[data-sort-control]");
    const resultsNode = document.querySelector("[data-results-count]");
    const emptyState = document.querySelector("[data-empty-state]");
    const filterBadge = document.querySelector("[data-active-filters]");
    const mobilePanel = document.querySelector("[data-filters-panel]");
    const filterToggle = document.querySelector("[data-filters-toggle]");

    const syncControls = (controls, value, source) => {
      controls.forEach((control) => {
        if (control !== source) {
          control.value = value;
        }
      });
    };

    const getFilters = () => ({
      category: categoryControls[0]?.value || "All",
      size: sizeControls[0]?.value || "All",
      condition: conditionControls[0]?.value || "All",
      sort: sortControl?.value || "featured",
    });

    const applyFilters = () => {
      const filters = getFilters();
      const visible = cards.filter((card) => {
        const matchesCategory = filters.category === "All" || card.dataset.category === filters.category;
        const matchesSize = filters.size === "All" || card.dataset.size === filters.size;
        const matchesCondition = filters.condition === "All" || card.dataset.condition === filters.condition;
        const shouldShow = matchesCategory && matchesSize && matchesCondition;
        card.classList.toggle("is-hidden", !shouldShow);
        return shouldShow;
      });

      const sorted = visible.slice().sort((left, right) => {
        if (filters.sort === "price-low") return Number(left.dataset.price) - Number(right.dataset.price);
        if (filters.sort === "price-high") return Number(right.dataset.price) - Number(left.dataset.price);
        if (filters.sort === "new") return Number(right.dataset.new) - Number(left.dataset.new);
        return Number(right.dataset.featured) - Number(left.dataset.featured);
      });

      sorted.forEach((card) => grid.appendChild(card));

      const activeFilters = [filters.category, filters.size, filters.condition].filter((value) => value !== "All").length;
      if (filterBadge) {
        filterBadge.textContent = String(activeFilters);
        filterBadge.classList.toggle("is-hidden", activeFilters === 0);
      }

      document.querySelectorAll("[data-clear-filters]").forEach((button) => {
        button.classList.toggle("is-hidden", activeFilters === 0);
      });

      if (resultsNode) {
        resultsNode.textContent = String(visible.length);
      }
      if (emptyState) {
        emptyState.classList.toggle("is-hidden", visible.length > 0);
      }

      const params = new URLSearchParams();
      if (filters.category !== "All") params.set("category", filters.category);
      if (filters.size !== "All") params.set("size", filters.size);
      if (filters.condition !== "All") params.set("condition", filters.condition);
      if (filters.sort !== "featured") params.set("sort", filters.sort);
      const query = params.toString();
      const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}`;
      window.history.replaceState({}, "", nextUrl);
    };

    const clearFilters = () => {
      categoryControls.forEach((control) => (control.value = "All"));
      sizeControls.forEach((control) => (control.value = "All"));
      conditionControls.forEach((control) => (control.value = "All"));
      if (sortControl) sortControl.value = "featured";
      applyFilters();
    };

    [...categoryControls, ...sizeControls, ...conditionControls].forEach((control) => {
      control.addEventListener("change", () => {
        if (control.dataset.filterControl === "category") syncControls(categoryControls, control.value, control);
        if (control.dataset.filterControl === "size") syncControls(sizeControls, control.value, control);
        if (control.dataset.filterControl === "condition") syncControls(conditionControls, control.value, control);
        applyFilters();
      });
    });

    if (sortControl) {
      sortControl.addEventListener("change", applyFilters);
    }

    document.querySelectorAll("[data-clear-filters]").forEach((button) => {
      button.addEventListener("click", clearFilters);
    });

    if (filterToggle && mobilePanel) {
      filterToggle.addEventListener("click", () => {
        mobilePanel.classList.toggle("is-open");
      });
    }

    applyFilters();
  };

  // Page animations: hero entrance, scroll reveals, and parallax backgrounds
  const initAnimations = () => {
    // Hero entrance animation
    const heroContent = document.querySelector('.hero__content');
    if (heroContent) {
      heroContent.classList.add('animate-in');
      // play after a short delay for a smooth entrance
      window.setTimeout(() => heroContent.classList.add('play'), 80);
    }

    // Intersection observer for reveal on scroll
    const revealSelectors = ['.section', '.value-card', '.product-card', '.banner__content', '.section-heading'];
    const revealElements = Array.from(document.querySelectorAll(revealSelectors.join(','))).filter(Boolean);

    if (revealElements.length > 0 && 'IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            entry.target.classList.remove('is-animated');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });

      revealElements.forEach((el) => {
        // avoid animating elements that are already visible
        if (el.getBoundingClientRect().top < window.innerHeight) {
          el.classList.add('revealed');
        } else {
          el.classList.add('is-animated');
          io.observe(el);
        }
      });
    }

    // Parallax for hero/banner backgrounds with enhanced zoom
    const parallaxImages = Array.from(document.querySelectorAll('.hero__media img, .banner__media img'));
    if (parallaxImages.length > 0) {
      let ticking = false;
      const onScroll = () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(() => {
          parallaxImages.forEach((img) => {
            const rect = img.parentElement.getBoundingClientRect();
            const center = rect.top + rect.height / 2 - window.innerHeight / 2;
            // larger zoom range for more dramatic effect, slower transition
            const translate = Math.max(-28, Math.min(28, -center * 0.05));
            const scale = 1.08 + Math.abs(center * 0.0002);
            img.style.transition = 'transform 2000ms linear';
            img.style.transform = `translateY(${translate}px) scale(${scale})`;
          });
          ticking = false;
        });
      };

      // initial call
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onScroll);
    }
  };

  initMenu();
  initUserMenu();
  initSimpleForms();
  initAddToCartButtons();
  initWishlistButtons();
  initGallery();
  initCartActions();
  initCheckoutFlow();
  initPriceAlerts();
  initWishlistPage();
  initContactForm();
  initShopFilters();
  initAnimations();
  refreshCartCount();
  renderCartPage();
  refreshWishlistCount();
})();
