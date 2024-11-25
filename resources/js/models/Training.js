import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class Training{
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new Training(model.id, model.name, model.competence_id);
    }

    constructor(id = null, name = '', competenceId = null) {
        this.id = id;
        this.name = name;
        this.competenceId = competenceId

        this.dataStructure = {
            id: null,
            name: 'required',
            competenceId: 'required'
        }
    }
}
