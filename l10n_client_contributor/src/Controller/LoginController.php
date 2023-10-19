<?php declare(strict_types = 1);

namespace Drupal\l10n_client_contributor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\l10n_client_contributor\L10nClientContributorHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Localization Client Contributor routes.
 */
final class LoginController extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly L10nClientContributorHelperInterface $l10nClientContributorHelper,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('l10n_client_contributor.helper'),
    );
  }

  /**
   * Builds the response.
   */
  public function __invoke(): array {
    if (!$this->l10nClientContributorHelper->isAuthenticated()) {
      $this->l10nClientContributorHelper->getAccessToken();
    }

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
