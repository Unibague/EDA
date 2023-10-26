import {checkIfModelHasEmptyProperties, toObjectRequest} from "@/HelperFunctions";

export default class AssessmentPeriod {
    toObjectRequest() {
        return toObjectRequest(this);
    }

    hasEmptyProperties() {
        return checkIfModelHasEmptyProperties(this);
    }

    static fromModel(model) {
        return new AssessmentPeriod(model.id, model.name, model.assessment_start_date, model.assessment_end_date, model.commitment_start_date, model.commitment_end_date);
    }

    constructor(id = null, name = '', assessmentStartDate = '', assessmentEndDate = '', commitmentStartDate = '', commitmentEndDate = '') {
        this.id = id;
        this.name = name;
        this.assessmentStartDate = assessmentStartDate;
        this.assessmentEndDate = assessmentEndDate;
        this.commitmentStartDate = commitmentStartDate;
        this.commitmentEndDate = commitmentEndDate;

        this.dataStructure = {
            id: null,
            name: 'required',
            assessmentStartDate: 'required',
            assessmentEndDate: 'required',
            commitmentStartDate: 'required',
            commitmentEndDate: 'required',
        }
    }
}
