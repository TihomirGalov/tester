    function createSlider(container, name, text, plural, initialValue) {
        let label = $('<label>', {
            class: 'form-label mt-3',
            for: name,
        }).text(text);
        container.append(label);
        let slider = $('<input>', {
            type: 'range',
            class: 'form-range',
            name: plural,
            min: -5,
            max: 5,
            value: initialValue,
            oninput: 'this.nextElementSibling.value = this.value'
        });
        container.append(slider);
        const value = $('<output>', {
            class: 'form-range-value',
            for: name,
            value: initialValue
        }).text(initialValue);
        container.append(value);
    }

    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        const testId = urlParams.get('test_id');
        $.ajax({
            url: `../src/fetch_results.php?test_id=${testId}`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                let reviewsForm = $('#reviewsForm');
                Object.keys(data).forEach(questionId => {
                    let questionData = data[questionId];
                    let questionContainer = $('<div>', {class: 'question-container mb-4'});
                    let questionTitle = $('<h5>').text(questionData.question);
                    questionContainer.append(questionTitle);
                    questionContainer.append('<input type="hidden" name="question_ids[]" value="' + questionId + '">');

                    questionData.answers.forEach(answer => {
                        let answerDiv = $('<div>', {class: 'form-check'});
                        let answerLabelClass = '';

                        if (answer.is_correct) {
                            answerLabelClass = 'text-success'; // Green text for correct answers
                        } else if (answer.user_answer) {
                            answerLabelClass = 'text-danger'; // Red text for incorrect answers
                        }

                        let answerInput = $('<input>', {
                            type: 'radio',
                            class: 'form-check-input',
                            name: 'answer_' + questionId,
                            value: answer.answer_id,
                            disabled: true,
                            checked: answer.user_answer == '1'
                        });

                        let answerLabel = $('<label>', {class: 'form-check-label ' + answerLabelClass}).text(answer.answer);
                        answerDiv.append(answerInput);
                        answerDiv.append(answerLabel);
                        questionContainer.append(answerDiv);
                    });

                    let feedbackText = questionData.answers.some(answer => answer.user_answer && answer.is_correct)
                        ? questionData.feedback_correct
                        : questionData.feedback_incorrect;

                    let feedbackDiv = $('<div>', {
                        class: 'alert ' + (feedbackText === questionData.feedback_correct ? 'alert-success' : 'alert-danger'),
                        role: 'alert'
                    }).text(feedbackText);

                    questionContainer.append(feedbackDiv);
                    createSlider(questionContainer, 'rating', 'Rate the question:', 'ratings[]', questionData.rating);
                    let br = $('<br>');
                    questionContainer.append(br);
                    createSlider(questionContainer, 'difficulty', 'Rate the difficulty:', 'difficulties[]', questionData.difficulty);

                    let timeTaken = $('<input>', {
                        type: 'number',
                        class: 'form-control mt-2',
                        name: 'time_taken[]',
                        placeholder: 'Time taken in seconds',
                        value: questionData.time_taken,
                    });
                    questionContainer.append(timeTaken);

                    let reviewTextarea = $('<textarea>', {
                        class: 'form-control mt-2',
                        name: 'reviews[]',
                        rows: 2,
                        placeholder: 'Leave your review here',
                    }).text(questionData.review);
                    questionContainer.append(reviewTextarea);
                    reviewsForm.append(questionContainer);
                });

                reviewsForm.append('<button type="submit" class="btn btn-primary mt-3">Save Review</button>');
            },
            error: function (xhr, status, error) {
                console.error('Error fetching results:', error);
                console.log(xhr.responseText); // Log the complete response text
            }
        });
    });