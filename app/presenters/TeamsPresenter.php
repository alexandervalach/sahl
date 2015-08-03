<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;

class TeamsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $teamRow;

    /** @var string */
    private $error = "Team not found";

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }
    
    public function renderDelete($id) {
        if (!$this->teamRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->team = $this->teamRow; 
        $this->getComponent('deleteForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->teamRow)
            throw new BadRequestException($this->error);
        
        $this->getComponent('editTeamForm')->setDefaults($this->teamRow);
        $this->template->team = $this->teamRow;
    }

    public function actionAll() {
        
    }

    public function renderAll() {
        /** $team is instance of Nette\Database\Table\Selection */
        $team = $this->teamsRepository->findAll()->order("name ASC");
        $this->template->teams = $team;
    }

    protected function createComponentAddTeamForm() {
        $form = new Form;

        $form->addText('name', 'Tím: ')
                ->setRequired('Názov tímu je povinné pole.')
                ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddTeamForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditTeamForm() {
        $form = new Form;

        $form->addText('name', 'Tím: ')
                ->setRequired('Názov tímu je povinné pole.')
                ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditTeamForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $players = $this->teamRow->related('players');

        foreach ($players as $player) {
            $player->delete();
        }

        $this->teamRow->delete();
        $this->flashMessage('Tím bol odstránený aj so všetkými hráčmi.', 'success');
        $this->redirect('all');
    }

    public function submittedAddTeamForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->teamsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditTeamForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->teamRow->update($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
