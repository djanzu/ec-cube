<?php

namespace Eccube\Controller\Admin\Setting\Shop;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\Admin\OrderStatusSettingType;
use Eccube\Form\Type\Admin\TradeLawType;
use Eccube\Form\Type\Front\ForgotType;
use Eccube\Repository\TradeLawRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TradeLawController extends AbstractController
{
    private TradeLawRepository $tradeLawRepository;

    /**
     * @param TradeLawRepository $tradeLawRepository
     */
    public function __construct(
        TradeLawRepository $tradeLawRepository
    ) {
        $this->tradeLawRepository = $tradeLawRepository;
    }

    /**
     * 税率設定の初期表示・登録
     *
     * @Route("/%eccube_admin_route%/setting/shop/tradelaw", name="admin_setting_shop_tradelaw", methods={"GET", "POST"})
     * @Template("@admin/Setting/Shop/tradelaw.twig")
     * @param Request $request
     */
    public function index(Request $request)
    {
        $tradeLawDetails = $this->tradeLawRepository->findBy([], ['sortNo' => 'DESC']);
        $builder = $this->formFactory->createBuilder();
        $builder
            ->add(
                'TradeLaws',
                CollectionType::class,
                [
                    'entry_type' => TradeLawType::class,
                    'data' => $tradeLawDetails,
                ]
            );
        $form = $builder->getForm();
        $form->handleRequest($request);

        $event = new EventArgs(
            [
                'TradeLaw' => $tradeLawDetails,
            ],
            $request
        );



        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form['TradeLaws'] as $child) {
                $OrderStatus = $child->getData();
                $this->entityManager->persist($OrderStatus);
            }
            $this->entityManager->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_TRADE_LAW_POST_COMPLETE);

            return $this->redirectToRoute('admin_setting_shop_tradelaw');
        }

        $this->eventDispatcher->dispatch($event, EccubeEvents::ADMIN_SETTING_SHOP_TRADE_LAW_INDEX_COMPLETE);

        return [
            'form' => $form->createView(),
            'tradeLawDetails' => $tradeLawDetails
        ];
    }
}
