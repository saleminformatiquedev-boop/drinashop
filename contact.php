<?php include 'includes/header.php'; ?>

<main style="padding-top: 4rem;">
  <section class="container">
    <h1 class="section-title">Nous Contacter</h1>
    <div class="content-section">
      <form onsubmit="event.preventDefault(); alert('Message envoyé !');">
        <div class="form-group">
          <label for="name">Nom complet</label>
          <input type="text" id="name" required placeholder="Votre nom" />
        </div>
        <div class="form-group">
          <label for="email">Adresse Email</label>
          <input type="email" id="email" required placeholder="Votre email" />
        </div>
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" rows="5" required placeholder="Comment pouvons-nous vous aider ?"></textarea>
        </div>
        <button type="submit" class="btn-primary" style="width: 100%;">Envoyer le message</button>
      </form>
    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
