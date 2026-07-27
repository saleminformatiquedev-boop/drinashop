<?php 
$page_title = 'Conditions Générales de Vente - Drinashop';
$page_description = 'Lisez nos conditions générales de vente, d\'utilisation et notre politique de paiement à la livraison sur Drinashop.';
include 'includes/header.php'; 
?>

<div class="container" style="padding: 4rem 1rem; max-width: 800px; margin: 0 auto; background: var(--bg-cream); border-radius: 10px; margin-top: 2rem;">
    <h1 style="font-family: var(--font-heading); color: var(--secondary-color); text-align: center; margin-bottom: 2rem;">Conditions Générales de Vente</h1>
    
    <div class="content" style="color: var(--text-color); line-height: 1.8;">
        
        <h2 style="color: var(--primary-color); margin-top: 2rem;">1. Introduction</h2>
        <p>Les présentes Conditions Générales de Vente (CGV) s'appliquent à toutes les commandes passées sur la boutique en ligne <strong>Drinashop</strong>. En confirmant votre commande, vous acceptez sans réserve ces conditions.</p>
        
        <h2 style="color: var(--primary-color); margin-top: 2rem;">2. Nos Produits</h2>
        <p>Drinashop propose une sélection de produits artisanaux authentiques des îles Kerkennah. Les photographies illustrant les produits n'entrent pas dans le champ contractuel. Si des erreurs s'y sont introduites, en aucun cas la responsabilité de Drinashop ne pourra être engagée.</p>

        <h2 style="color: var(--primary-color); margin-top: 2rem;">3. Prix et Commandes</h2>
        <p>Les prix de nos produits sont indiqués en Dinar Tunisien (DT) toutes taxes comprises, sauf indication contraire. Toute commande passée sur le site engage le client dès la validation de celle-ci.</p>

        <h2 style="color: var(--primary-color); margin-top: 2rem;">4. Modalité de Paiement (Paiement à la Livraison)</h2>
        <p>Afin de faciliter vos achats et garantir votre sécurité, <strong>Drinashop propose exclusivement le paiement à la livraison</strong>.</p>
        <ul style="margin-left: 2rem; margin-bottom: 1rem;">
            <li>Le règlement de votre commande s'effectue en <strong>espèces</strong> directement auprès du livreur au moment de la réception de votre colis.</li>
            <li>Aucun paiement préalable par carte bancaire ou virement n'est requis sur notre site.</li>
            <li>Merci de préparer le montant exact pour faciliter la transaction lors de la livraison.</li>
        </ul>

        <h2 style="color: var(--primary-color); margin-top: 2rem;">5. Livraison</h2>
        <p>Les livraisons sont effectuées à l'adresse indiquée lors du processus de commande. Les délais de livraison sont fournis à titre indicatif. En cas de retard, Drinashop ne saurait être tenue pour responsable. Le client est tenu de vérifier l'état des produits lors de la livraison avant de procéder au paiement.</p>

        <h2 style="color: var(--primary-color); margin-top: 2rem;">6. Rétractation et Retours</h2>
        <p>Conformément à la réglementation en vigueur, vous disposez d'un délai de rétractation après la livraison. Cependant, comme le paiement s'effectue à la livraison, vous avez la possibilité de refuser le colis si celui-ci ne correspond pas à vos attentes au moment de sa présentation par le livreur.</p>

        <h2 style="color: var(--primary-color); margin-top: 2rem;">7. Service Client</h2>
        <p>Pour toute question ou information complémentaire, notre service client est à votre disposition. Vous pouvez nous contacter via la page <a href="<?= BASE_URL ?>/contact" style="color: var(--primary-color);">Contact</a> de notre site web.</p>
        
        <p style="margin-top: 3rem; text-align: center; color: var(--text-muted); font-size: 0.9rem;">Dernière mise à jour : <?= date('d/m/Y') ?></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
