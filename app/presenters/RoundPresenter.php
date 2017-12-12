<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RoundPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $roundRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Round not found!";

    public function renderAll() 
    {
        $this->redrawControl('main');
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', null);    
        $this['breadCrumb']->addLink("Kolá");
        if ($this->user->isLoggedIn()) {
            $this->getComponent('addRoundForm');
        }
    }

    public function actionEdit($id) 
    {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderEdit($id) 
    {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('editRoundForm')->setDefaults($this->roundRow);
    }

    public function actionDelete($id) 
    {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderDelete($id) 
    {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('deleteForm');
    }

    public function actionArchView($id) 
    {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) 
    {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
        $this['breadCrumb']->addLink("Archív", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Kolá");
    }

    protected function createComponentAddRoundForm() 
    {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::FILLED, "Opa, zabudli ste vyplniť názov kola");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedAddRoundForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditRoundForm() 
    {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditRoundForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddRoundForm(Form $form, $values) 
    {
        $this->roundsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditRoundForm(Form $form, $values) 
    {
        $this->roundRow->update($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() 
    {
        $fights = $this->roundRow->related('fights');
        foreach ($fights as $fight) {
            $fight->delete();
        }
        $this->roundRow->delete();
        $this->flashMessage('Kolo bolo odstránené.', 'success');
        $this->redirect('all');
    }

    public function formCancelled() 
    {
        $this->redirect('all');
    }

}
