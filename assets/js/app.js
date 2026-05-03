(() => {
  // Store cart data in localStorage
  const CART_KEY = "pastimes-cart";
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
  initGallery();
  initCartActions();
  initContactForm();
  initShopFilters();
  initAnimations();
  refreshCartCount();
  renderCartPage();
})();
