<?php 
$page_title = 'Contact - Drinashop : Produits artisanaux de Kerkennah';
$page_description = 'Une question sur nos produits artisanaux ? Contactez le support de Drinashop pour toute demande.';
include 'includes/header.php'; 
?>

<main style="padding-top: 4rem;">
  <section class="container">
    <h1 class="section-title">Nous Contacter</h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-top: 2rem;">
      
      <!-- Contact Info & Map -->
      <div class="contact-info" style="display: flex; flex-direction: column; gap: 2rem;">
        
        <!-- Info Cards -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
          
          <div style="display: flex; align-items: flex-start; gap: 1rem; background: var(--bg-cream); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div style="font-size: 2rem; color: var(--primary-color);">📍</div>
            <div>
              <h3 style="margin-bottom: 0.5rem; font-family: var(--font-heading); color: var(--secondary-color);">Adresse</h3>
              <p style="color: var(--text-muted); line-height: 1.5;">Route el Kraten, Mellita,<br>3015 Kerkennah (Sfax)</p>
            </div>
          </div>
          
          <div style="display: flex; align-items: flex-start; gap: 1rem; background: var(--bg-cream); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div style="font-size: 2rem; color: var(--primary-color);">📞</div>
            <div>
              <h3 style="margin-bottom: 0.5rem; font-family: var(--font-heading); color: var(--secondary-color);">Téléphone</h3>
              <p style="color: var(--text-muted); line-height: 1.5;"><a href="tel:+21654618653" style="color: inherit; text-decoration: none;">+216 54 618 653</a></p>
            </div>
          </div>
          
          <div style="display: flex; align-items: flex-start; gap: 1rem; background: var(--bg-cream); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-color);">
            <div style="font-size: 2rem; color: var(--primary-color);">🕒</div>
            <div>
              <h3 style="margin-bottom: 0.5rem; font-family: var(--font-heading); color: var(--secondary-color);">Horaires</h3>
              <p style="color: var(--text-muted); line-height: 1.5;">Ouvert tous les jours<br>de 8h00 à 18h00</p>
            </div>
          </div>

        </div>

        <!-- Google Maps iframe -->
        <div style="width: 100%; height: 350px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid var(--border-color);">
          <iframe 
            src="https://maps.google.com/maps?q=Route+el+Kraten,+Mellita,+3015+Kerkennah&t=&z=14&ie=UTF8&iwloc=&output=embed" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
        
      </div>

      <!-- Contact Form -->
      <div class="content-section" style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid var(--border-color); height: fit-content;">
        <h2 style="font-family: var(--font-heading); color: var(--secondary-color); margin-bottom: 2rem; font-size: 2rem;">Envoyez-nous un message</h2>
        <form onsubmit="event.preventDefault(); alert('Message envoyé !');">
          <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">Nom complet</label>
            <input type="text" id="name" required placeholder="Votre nom" style="width: 100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 1rem;" />
          </div>
          <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">Adresse Email</label>
            <input type="email" id="email" required placeholder="Votre email" style="width: 100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 1rem;" />
          </div>
          <div class="form-group" style="margin-bottom: 2rem;">
            <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary-color);">Message</label>
            <textarea id="message" rows="6" required placeholder="Comment pouvons-nous vous aider ?" style="width: 100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 1rem; resize: vertical;"></textarea>
          </div>
          <button type="submit" class="btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.1rem; border-radius: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Envoyer le message</button>
        </form>
      </div>

    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
