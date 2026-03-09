{{-- Feature strip + Footer --}}
<section class="feature-strip feature-strip--footer" aria-label="Store policies">
  <div class="container feature-strip__inner">
    <ul class="feature-strip__list" role="list">
      <li class="feature-strip__item">
        <p class="feature-strip__title">FREE SHIPPING ON ORDERS $50+</p>
        <p class="feature-strip__text">Orders placed after 10am MST are typically shipped within 1–2 business days</p>
        <a class="feature-strip__link" href="/shipping/">Shipping Policy <span aria-hidden="true">→</span></a>
      </li>
      <li class="feature-strip__item">
        <p class="feature-strip__title">HASSLE-FREE EXCHANGES</p>
        <p class="feature-strip__text">Starting and managing returns is a breeze with our easy and efficient process. Shop stress-free and worry-free.</p>
        <a class="feature-strip__link" href="/help/">Help Center <span aria-hidden="true">→</span></a>
      </li>
      <li class="feature-strip__item">
        <p class="feature-strip__title">30-DAY RETURN PERIOD</p>
        <p class="feature-strip__text">Experience worry-free shopping with our 30-day return policy. We believe in the quality of our products and want you to feel the same.</p>
        <a class="feature-strip__link" href="/returns/">Refund Policy <span aria-hidden="true">→</span></a>
      </li>
    </ul>
  </div>
</section>

<footer class="site-footer" aria-label="Footer">
  <div class="container footer-top">
    <div class="footer-brand">
      <div class="footer-logo" aria-hidden="true">
        <svg width="46" height="46" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L20.5 5.5V12.6C20.5 17.1 17.6 20.2 12 22C6.4 20.2 3.5 17.1 3.5 12.6V5.5L12 2Z" stroke="currentColor" stroke-width="1.6"/>
          <path d="M9.6 7.1H13.5C15.2 7.1 16.4 8.0 16.4 9.5C16.4 10.6 15.8 11.2 14.9 11.5C16.0 11.8 16.8 12.7 16.8 14.0C16.8 15.8 15.4 16.9 13.5 16.9H9.6V7.1ZM11.4 11.0H13.3C14.2 11.0 14.6 10.6 14.6 9.9C14.6 9.2 14.2 8.8 13.3 8.8H11.4V11.0ZM11.4 15.2H13.4C14.3 15.2 14.9 14.7 14.9 13.9C14.9 13.2 14.3 12.7 13.4 12.7H11.4V15.2Z" fill="currentColor"/>
        </svg>
      </div>

      <p class="footer-brand__name">BE BETTER. BSBL.</p>
      <p class="footer-brand__desc">
        Where Resilience Meets Lifestyle. Born in a garage in 2010, we offer high-quality activewear, lifestyle essentials, and durable gear for the relentless pursuit of progress.
      </p>

      <div class="footer-social" aria-label="Social links">
        <a class="footer-social__icon" href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
          <span aria-hidden="true">f</span>
        </a>
        <a class="footer-social__icon" href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
          <span aria-hidden="true">⌁</span>
        </a>
        <a class="footer-social__icon" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
          <span aria-hidden="true">▶</span>
        </a>
        <a class="footer-social__icon" href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
          <span aria-hidden="true">♪</span>
        </a>
      </div>
    </div>

    <div class="footer-cols">
      <div class="footer-col">
        <p class="footer-col__title">SHOP</p>
        <a class="footer-link" href="/collections/men/">MENS</a>
        <a class="footer-link" href="/collections/women/">WOMENS</a>
        <a class="footer-link" href="/collections/gear/">GEAR</a>
        <a class="footer-link" href="/collections/sale/">SALE</a>
        <a class="footer-link" href="/collections/men/">EXPLORE</a>
      </div>

      <div class="footer-col">
        <p class="footer-col__title">ABOUT</p>
        <a class="footer-link" href="/about/">ABOUT US</a>
        <a class="footer-link" href="/contact/">CONTACT US</a>
        <a class="footer-link" href="/careers/">CAREERS</a>
        <a class="footer-link" href="/accessibility/">ACCESSIBILITY</a>
        <a class="footer-link" href="/returns/">RETURNS</a>
        <a class="footer-link" href="/shipping/">SHIPPING</a>
        <a class="footer-link" href="/privacy/">PRIVACY POLICY</a>
        <a class="footer-link" href="/terms/">TERMS OF SERVICE</a>
      </div>

      <div class="footer-col">
        <p class="footer-col__title">CONTACT US</p>
        <a class="footer-link footer-link--accent" href="/help/">HELP CENTER</a>
        <p class="footer-contact__hours">Open Mon-Fri, 7am-3pm MST</p>
      </div>
    </div>
  </div>

  <div class="container footer-divider" aria-hidden="true"></div>

  <div class="container footer-bottom">
    <div class="footer-bottom__row">
      <div class="footer-locale">
        <label class="footer-locale__label" for="country">Country/region</label>
        <div class="footer-locale__selectwrap">
          <select class="select footer-locale__select" id="country" name="country">
            <option selected>United States | USD $</option>
            <option>Canada | CAD $</option>
            <option>United Kingdom | GBP £</option>
          </select>
        </div>
      </div>

      <div class="footer-payments" aria-label="Payment methods">
        <span class="pay-icon" aria-hidden="true">AMEX</span>
        <span class="pay-icon" aria-hidden="true">APPLEPAY</span>
        <span class="pay-icon" aria-hidden="true">DISC</span>
        <span class="pay-icon" aria-hidden="true">MC</span>
        <span class="pay-icon" aria-hidden="true">PAYPAL</span>
        <span class="pay-icon" aria-hidden="true">SHOP</span>
        <span class="pay-icon" aria-hidden="true">VISA</span>
      </div>
    </div>

    <div class="footer-legal">
      <span>&copy; {{ date('Y') }}, FLAG NOR FAIL</span>
      <span class="footer-legal__sep" aria-hidden="true">-</span>
      <a href="/returns/">Refund policy</a>
      <span class="footer-legal__sep" aria-hidden="true">-</span>
      <a href="/privacy/">Privacy policy</a>
      <span class="footer-legal__sep" aria-hidden="true">-</span>
      <a href="/terms/">Terms of service</a>
      <span class="footer-legal__sep" aria-hidden="true">-</span>
      <a href="/shipping/">Shipping policy</a>
      <span class="footer-legal__sep" aria-hidden="true">-</span>
      <a href="/contact/">Contact information</a>
    </div>
  </div>
</footer>

