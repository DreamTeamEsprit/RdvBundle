<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    public function indexAction()
    {


        $role = "";

        if (is_object($this->getUser())) {
            $iduser = $this->getUser()->getId();
            $em = $this->getDoctrine()->getManager();
            $req = $em->getConnection()->prepare("select type from utilisateur where id= :iduser");
            $req->execute([
                'iduser' => $iduser
            ]);
            $datamois = $req->fetch();
            $role = $datamois['type'];
            foreach ($this->getUser()->getRoles() as $item) {
                if ($item == "ROLE_SUPER_ADMIN") {
                    return $this->render('@User/Admin/Dashboard.html.twig');
                }
            }
        }
        $session = new Session();

        $session->set('role', $role);

        return $this->render('@User/layout.html.twig', array(
            'role' => $role
        ));


    }

    public function dashboardAction()
    {
        return $this->render('@User/Admin/Dashboard.html.twig');
    }

    public function departementsAction()
    {
        return $this->render('@User/Default/Departements.html.twig');
    }

    public function evenementsAction()
    {
        return $this->render('@User/Default/Evenements.html.twig');
    }

    public function forumAction()
    {
        return $this->render('@User/Default/Forum.html.twig');
    }

    public function reclamationsAction()
    {
        return $this->render('@User/Default/Reclamations.html.twig');
    }

    public function contactsAction()
    {
        return $this->render('@User/Default/Contacts.html.twig');
    }

    public function espaceClientAction()
    {
        $m = $this->getDoctrine()->getManager();
        $user = $m->getRepository('UserBundle:User')->find($this->getUser()->getId());
        $rdv = $user->getRdv();
        //$specialiste = $rdv->getCalendrier()->getProfessionnel();
        $departements = $m->getRepository('UserBundle:departement')->findAll();

        $rdvsg = $m->createQueryBuilder()->select('r')
            ->from('UserBundle\Entity\rdv', 'r')
            ->where('r.user= ' . $this->getUser()->getId())
            ->groupBy('r.calendrier')
            ->getQuery()->getResult();

        return $this->render('@User/Specialiste/EspaceClient.html.twig',
            array(
                'rdvs' => $rdv,
                'rdvsg' => $rdvsg,
                'departements' => $departements
            ));
    }

    public function AfficherMedecinAction()
    {
        $m = $this->getDoctrine()->getManager();
        $medecins = $m->getRepository('UserBundle:specialiste')->findAll();

        return $this->render('@User/Admin/Specialiste/gestionSpecialiste.html.twig',
            array(
                'medecins' => $medecins,
            )
        );
    }

    function supprimerMedecinAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $medecin = $em->getRepository('UserBundle:User')->find($id);
        $em->remove($medecin);
        $em->flush();
        return $this->redirectToRoute('medecins');
    }

    public function bloquerMedecinAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $medecin = $em->getRepository('UserBundle:User')->find($id);
        $medecin->setEnabled(0);
        $em->persist($medecin);
        $em->flush();
        return $this->redirectToRoute('medecins');

    }

    public function debloquerMedecinAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $medecin = $em->getRepository('UserBundle:User')->find($id);
        $medecin->setEnabled(1);
        $em->persist($medecin);
        $em->flush();
        return $this->redirectToRoute('medecins');

    }

    public function ajouterMedecinAction()
    {

        return $this->render('@User/Admin/Specialiste/AjoutSpecialiste.html.twig');

    }
}